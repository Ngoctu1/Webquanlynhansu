<?php
namespace App\Services;

use App\Models\{Attendance, Employee, PayrollDetail, PayrollPeriod, User};
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class PayrollService
{
    public function candidates(PayrollPeriod $period)
    {
        [$start, $end] = $this->dates($period);
        return Employee::with('workSchedule')->where(function ($q) use ($start, $end, $period) {
            $q->whereHas('contracts', fn ($q) => $q->where('status', 'approved')->whereDate('effective_from', '<=', $end)
                ->where(fn ($q) => $q->whereNull('effective_to')->orWhereDate('effective_to', '>=', $start)))
                ->orWhereHas('attendances', fn ($q) => $q->whereBetween('work_date', [$start, $end]))
                ->orWhereHas('payrollDetails', fn ($q) => $q->where('payroll_period_id', $period->id));
        })->orderBy('id');
    }

    public function overtimeInputs(PayrollPeriod $period)
    {
        [$start, $end] = $this->dates($period);
        return Attendance::with(['employee.workSchedule', 'employee.contracts' => fn ($q) => $q->where('status', 'approved')
            ->whereDate('effective_from', '<=', $end)->where(fn ($q) => $q->whereNull('effective_to')->orWhereDate('effective_to', '>=', $start))])
            ->whereBetween('work_date', [$start, $end])->where('overtime_status', 'approved')->where('overtime_minutes', '>', 0)
            ->orderBy('work_date')->orderBy('employee_id')->get()->map(function ($row) use ($period) {
                $contract = $row->employee->contracts->count() === 1 ? $row->employee->contracts->first() : null;
                $days = WorkTimeCalculator::standardDays($period->year, $period->month, $row->employee->workSchedule->work_days);
                $row->suggested_hourly_rate = $contract ? PayrollCalculator::suggestedHourlyRate(
                    $contract->base_salary, $contract->salary_coefficient, $contract->allowance, $days, $row->employee->workSchedule->standard_minutes) : '';
                $row->minimum_multiplier = PayrollCalculator::minimumMultiplier($row->overtime_type);
                return $row;
            });
    }

    public function calculate(User $actor, PayrollPeriod $period, array $adjustments, array $overtime): void
    {
        abort_unless($actor->canManageHrm(), 403);
        DB::transaction(function () use ($actor, $period, $adjustments, $overtime) {
            $period = PayrollPeriod::lockForUpdate()->findOrFail($period->id);
            $this->state($period, ['draft', 'rejected']);
            $employees = $this->candidates($period)->lockForUpdate()->get();
            if ($employees->isEmpty()) { $this->fail('Không có nhân viên hoặc hợp đồng phù hợp kỳ lương.'); }
            $old = $period->details()->get()->toArray(); $ids = [];
            foreach ($employees as $employee) {
                $adjustment = $adjustments[$employee->id] ?? null;
                if (!$adjustment || !array_key_exists('deduction', $adjustment)) { $this->fail('Cần nhập khấu trừ cho '.$employee->employee_code.'.'); }
                if ((float)$adjustment['deduction'] > 0 && !trim((string)($adjustment['note'] ?? ''))) { $this->fail('Khoản khấu trừ cần ghi căn cứ.'); }
                [$fields, $basis] = $this->source($period, $employee);
                $otRows = []; $otAmounts = [];
                $suggested = PayrollCalculator::suggestedHourlyRate($fields['base_salary'], $fields['salary_coefficient'],
                    $fields['allowance'], (int)$fields['standard_days'], $basis['schedule']['standard_minutes']);
                foreach ($basis['attendance'] as $row) {
                    if ($row['overtime_status'] !== 'approved' || $row['overtime_minutes'] <= 0) { continue; }
                    $input = $overtime[$row['id']] ?? null;
                    if (!$input || empty($input['confirmed'])) { $this->fail('Cần HR xác nhận căn cứ OT ngày '.$row['work_date'].' của '.$employee->employee_code.'.'); }
                    $minimum = PayrollCalculator::minimumMultiplier($row['overtime_type']);
                    if (((float)$input['hourly_rate'] !== (float)$suggested || (float)$input['multiplier'] !== (float)$minimum)
                        && !trim((string)($input['reason'] ?? ''))) { $this->fail('Thay đổi gợi ý lương giờ hoặc hệ số OT cần ghi lý do.'); }
                    try {
                        $amount = PayrollCalculator::overtime((string)$input['hourly_rate'], (string)$input['multiplier'], $row['overtime_minutes'], $row['overtime_type']);
                    } catch (InvalidArgumentException $e) { $this->fail($employee->employee_code.': '.$e->getMessage()); }
                    $otAmounts[] = $amount;
                    $otRows[] = ['attendance_id' => $row['id'], 'date' => $row['work_date'], 'minutes' => $row['overtime_minutes'],
                        'type' => $row['overtime_type'], 'suggested_hourly_rate' => $suggested,
                        'hourly_rate' => (string)$input['hourly_rate'], 'multiplier' => (string)$input['multiplier'],
                        'amount' => $amount, 'reason' => $input['reason'] ?? null, 'confirmed_by' => $actor->id];
                }
                try {
                    $otPay = PayrollCalculator::sumMoney($otAmounts);
                    $totals = PayrollCalculator::calculate($fields['base_salary'], $fields['salary_coefficient'], $fields['allowance'],
                        $fields['standard_days'], $fields['actual_days'], $otPay, (string)$adjustment['deduction'],
                        $basis['regular_minutes'], $basis['schedule']['standard_minutes']);
                } catch (InvalidArgumentException $e) { $this->fail($employee->employee_code.': '.$e->getMessage()); }
                $detail = PayrollDetail::updateOrCreate(['payroll_period_id' => $period->id, 'employee_id' => $employee->id], [
                    ...$fields, 'overtime_pay' => $otPay, 'deduction' => $adjustment['deduction'], 'note' => $adjustment['note'] ?? null, ...$totals,
                    'calculation_snapshot' => ['version' => 1, 'source' => $basis, 'source_hash' => $this->fingerprint($basis),
                        'overtime' => $otRows, 'confirmed_by' => $actor->id, 'confirmed_at' => now()->toIso8601String()],
                ]);
                $ids[] = $detail->id;
            }
            $period->details()->whereNotIn('id', $ids)->delete();
            AuditService::record('PAYROLL_CALCULATED', $period, ['details' => $old], ['details' => $period->details()->get()->toArray()]);
        }, 3);
    }

    public function transition(User $actor, PayrollPeriod $period, string $action, ?string $reason = null): void
    {
        abort_unless($action === 'submit' ? $actor->canManageHrm() : $actor->canApprove(), 403);
        DB::transaction(function () use ($actor, $period, $action, $reason) {
            $period = PayrollPeriod::lockForUpdate()->findOrFail($period->id);
            $this->state($period, $action === 'submit' ? ['draft', 'rejected'] : ['pending_approval']);
            $old = $period->getAttributes();
            if (in_array($action, ['submit', 'approve'], true)) { $this->verifySnapshots($period); }
            $data = match ($action) {
                'submit' => ['status' => 'pending_approval', 'submitted_by' => $actor->id, 'submitted_at' => now(),
                    'approved_by' => null, 'approved_at' => null, 'rejection_reason' => null],
                'approve' => ['status' => 'published', 'approved_by' => $actor->id, 'approved_at' => now(), 'published_at' => now(), 'rejection_reason' => null],
                'reject' => ['status' => 'rejected', 'rejection_reason' => $reason, 'approved_by' => null, 'approved_at' => null],
            };
            if ($action === 'reject' && !trim((string)$reason)) { $this->fail('Cần lý do từ chối.'); }
            $period->update($data);
            AuditService::record('PAYROLL_'.strtoupper($action), $period, $old);
            if ($action === 'approve') { AuditService::record('PAYROLL_PUBLISHED', $period, $old); }
        }, 3);
    }

    private function verifySnapshots(PayrollPeriod $period): void
    {
        $employees = $this->candidates($period)->lockForUpdate()->get();
        $details = $period->details()->get()->keyBy('employee_id');
        if ($details->isEmpty() || $details->count() !== $employees->count()) { $this->fail('Cần tính đủ bảng lương trước khi trình hoặc duyệt.'); }
        foreach ($employees as $employee) {
            $detail = $details->get($employee->id);
            if (!$detail) { $this->fail('Thiếu chi tiết lương của '.$employee->employee_code.'.'); }
            if ($detail->calculation_snapshot['requires_recalculation'] ?? false) {
                $this->fail($employee->employee_code.': công đã được điều chỉnh. HR phải tính lại bảng lương trước khi trình duyệt.');
            }
            [$fields, $basis] = $this->source($period, $employee);
            if (($detail->calculation_snapshot['source_hash'] ?? '') !== $this->fingerprint($basis)) {
                $this->fail('Nguồn công hoặc căn cứ lương đã thay đổi. HR cần tính lại trước khi trình duyệt.');
            }
            foreach ($fields as $field => $value) {
                if ((string)$detail->{$field} !== (string)$value) { $this->fail('Snapshot lương đã cũ; cần tính lại.'); }
            }
        }
    }

    private function source(PayrollPeriod $period, Employee $employee): array
    {
        [$start, $end] = $this->dates($period);
        $contracts = $employee->contracts()->where('status', 'approved')->whereDate('effective_from', '<=', $end)
            ->where(fn ($q) => $q->whereNull('effective_to')->orWhereDate('effective_to', '>=', $start))->get();
        if ($contracts->count() !== 1) { $this->fail($employee->employee_code.': cần đúng một hợp đồng đã duyệt trong tháng; nhiều hợp đồng/tháng chưa được schema hỗ trợ.'); }
        $contract = $contracts->first(); $schedule = $employee->workSchedule;
        $days = WorkTimeCalculator::standardDays($period->year, $period->month, $schedule->work_days);
        if ($days !== (int)$period->standard_work_days) { $this->fail('Lịch nhân viên có công chuẩn khác kỳ lương. Chưa hỗ trợ nhiều lịch khác công chuẩn trong cùng kỳ.'); }
        $attendance = Attendance::where('employee_id', $employee->id)->whereBetween('work_date', [$start, $end])->orderBy('work_date')->get();
        $byDate = $attendance->keyBy(fn ($row) => $row->work_date->toDateString());
        $first = max($start, $contract->effective_from->toDateString(), $employee->hire_date?->toDateString() ?? $start);
        $last = min($end, $contract->effective_to?->toDateString() ?? $end);
        for ($day = Carbon::parse($first); $day->toDateString() <= $last; $day->addDay()) {
            if (in_array($day->dayOfWeekIso, $schedule->work_days, true) && !$byDate->has($day->toDateString())) {
                $this->fail($employee->employee_code.': thiếu chấm công '.$day->format('d/m/Y').'. Chờ HR xác nhận ngày lễ/nghỉ phép/vắng mặt; không tự tính nghỉ không lương.');
            }
        }
        $minutes = 0; $records = [];
        foreach ($attendance as $row) {
            if ($row->status === 'checked_in') {
                $this->fail($employee->employee_code.': chấm công ngày '.$row->work_date->format('d/m/Y').' chưa hoàn tất. HR cần xử lý hoặc xác nhận công có lý do trước khi tính/trình lương.');
            }
            if ($row->overtime_status === 'pending') { $this->fail($employee->employee_code.': còn OT ngày '.$row->work_date->format('d/m/Y').' chờ HR xử lý.'); }
            if ($row->status === 'completed' && (!$row->check_in || !$row->check_out)) { $this->fail('Thiếu giờ vào/ra cần HR xử lý.'); }
            if ($row->status === 'adjusted' && (!$row->adjusted_by || !trim((string) $row->adjustment_reason))) {
                $this->fail($employee->employee_code.': quyết định công ngày '.$row->work_date->format('d/m/Y').' cần người xác nhận và lý do.');
            }
            if (($row->regular_minutes > 0 || ($row->overtime_status === 'approved' && $row->overtime_minutes > 0))
                && ($row->work_date->toDateString() < $first || $row->work_date->toDateString() > $last)) { $this->fail($employee->employee_code.': có công ngoài thời gian hợp đồng/nhận việc.'); }
            if ($row->check_in && $row->check_out &&
                ($row->check_in->format('H:i') < '06:00' || $row->check_out->format('H:i:s') > '22:00:00' || !$row->check_in->isSameDay($row->check_out))) {
                $this->fail('Có thời gian ban đêm/qua ngày chưa được công thức hiện tại hỗ trợ; cần quy trình xử lý riêng.');
            }
            $minutes += $row->regular_minutes;
            $records[] = ['id' => $row->id, 'work_date' => $row->work_date->toDateString(), 'regular_minutes' => $row->regular_minutes,
                'check_in' => $row->check_in?->toDateTimeString(), 'check_out' => $row->check_out?->toDateTimeString(),
                'late_minutes' => $row->late_minutes, 'status' => $row->status, 'adjustment_reason' => $row->adjustment_reason,
                'adjusted_by' => $row->adjusted_by, 'overtime_status' => $row->overtime_status, 'overtime_type' => $row->overtime_type,
                'overtime_minutes' => $row->overtime_minutes, 'overtime_approved_by' => $row->overtime_approved_by];
        }
        if ($minutes > $days * $schedule->standard_minutes) { $this->fail($employee->employee_code.': tổng phút công vượt chuẩn tháng.'); }
        $fields = ['contract_id' => $contract->id, 'base_salary' => $contract->base_salary, 'salary_coefficient' => $contract->salary_coefficient,
            'allowance' => $contract->allowance, 'standard_days' => number_format($days, 2, '.', ''),
            'actual_days' => PayrollCalculator::daysFromMinutes($minutes, $schedule->standard_minutes)];
        $basis = ['contract' => $contract->only(['id','contract_code','effective_from','effective_to','base_salary','salary_coefficient','allowance']),
            'schedule' => $schedule->only(['id','start_time','end_time','break_start','break_end','standard_minutes','late_grace_minutes','work_days']),
            'regular_minutes' => $minutes, 'attendance' => $records];
        return [$fields, $basis];
    }

    private function fingerprint(array $basis): string { return hash('sha256', json_encode($basis, JSON_THROW_ON_ERROR)); }
    public function dates(PayrollPeriod $period): array
    {
        $date = Carbon::create($period->year, $period->month, 1)->startOfDay();
        return [$date->toDateString(), $date->endOfMonth()->toDateString()];
    }
    private function state(PayrollPeriod $period, array $allowed): void
    {
        if (!in_array($period->status, $allowed, true)) { $this->fail('Kỳ lương đã khóa hoặc không ở trạng thái phù hợp.'); }
    }
    private function fail(string $message): never { throw ValidationException::withMessages(['payroll' => $message]); }
}
