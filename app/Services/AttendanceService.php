<?php
namespace App\Services;

use App\Models\{Attendance, Employee, PayrollPeriod, User};
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class AttendanceService
{
    public function checkIn(User $user): void
    {
        DB::transaction(function () use ($user) {
            $now = now();
            $this->lockPeriod($now);
            $employee = Employee::with('workSchedule')->lockForUpdate()->findOrFail($user->employee_id);
            $this->assertAttendanceAccess($user, $employee);
            try {
                WorkTimeCalculator::ensureCheckInAllowed($employee->workSchedule->toArray(), $now);
            } catch (InvalidArgumentException $e) { $this->fail($e->getMessage()); }
            // Only today's record prevents a new check-in; older incomplete records remain for HR.
            if ($employee->attendances()->whereDate('work_date', $now)->exists()) { $this->fail('Bạn đã có bản ghi chấm công hôm nay.'); }
            $row = Attendance::create(['employee_id' => $employee->id, 'work_date' => $now->toDateString(),
                'check_in' => $now, 'status' => 'checked_in', 'regular_minutes' => 0, 'work_units' => 0,
                'late_minutes' => 0, 'overtime_minutes' => 0, 'overtime_hours' => 0, 'overtime_status' => 'none']);
            AuditService::record('ATTENDANCE_CHECK_IN', $row);
        }, 3);
    }

    public function checkOut(User $user): void
    {
        DB::transaction(function () use ($user) {
            $now = now();
            $this->lockPeriod($now);
            $employee = Employee::with('workSchedule')->lockForUpdate()->findOrFail($user->employee_id);
            $this->assertAttendanceAccess($user, $employee);
            $row = Attendance::where('employee_id', $user->employee_id)->whereDate('work_date', $now)->lockForUpdate()->first();
            if (!$row) {
                $hasMissingCheckout = Attendance::where('employee_id', $user->employee_id)
                    ->whereDate('work_date', '<', $now->toDateString())->whereNotNull('check_in')
                    ->whereNull('check_out')->where('status', 'checked_in')->exists();
                if ($hasMissingCheckout) {
                    $this->fail('Đã quá thời gian check-out của ngày làm việc này. Vui lòng liên hệ HR để được hỗ trợ điều chỉnh chấm công.');
                }
                $this->fail('Bạn chưa thực hiện check-in.');
            }
            if (!$row->check_in) { $this->fail('Bạn chưa thực hiện check-in.'); }
            if ($row->check_out) { $this->fail('Bạn đã thực hiện check-out cho ngày làm việc này.'); }
            if ($row->status !== 'checked_in') { $this->fail('Bản ghi đã được HR xử lý, không thể check-out lại.'); }
            $old = $row->getAttributes();
            try {
                WorkTimeCalculator::ensureCheckOutAllowed($employee->workSchedule->toArray(), $row->work_date->toDateString(), $row->check_in->toDateTimeString(), $now);
                $computed = WorkTimeCalculator::calculate($employee->workSchedule->toArray(), $row->work_date->toDateString(), $row->check_in->toDateTimeString(), $now->toDateTimeString());
            } catch (InvalidArgumentException $e) { $this->fail($e->getMessage()); }
            $row->update([...$computed, 'check_out' => $now, 'status' => 'completed']);
            AuditService::record('ATTENDANCE_CHECK_OUT', $row, $old);
        }, 3);
    }

    public function adjust(User $actor, array $data, ?Attendance $attendance = null): void
    {
        abort_unless($actor->canManageHrm(), 403);
        if (!trim((string) ($data['adjustment_reason'] ?? ''))) { $this->fail('Điều chỉnh công phải có lý do hoặc quyết định công hưởng lương.'); }
        DB::transaction(function () use ($actor, $data, $attendance) {
            $date = Carbon::parse($attendance?->work_date ?? $data['work_date']);
            $period = $this->lockPeriod($date);
            $employee = Employee::with('workSchedule')->lockForUpdate()->findOrFail($attendance?->employee_id ?? $data['employee_id']);
            $schedule = $employee->workSchedule;
            if ($data['regular_minutes'] > $schedule->standard_minutes) { $this->fail('Phút công hưởng lương vượt thời gian chuẩn của lịch làm việc.'); }
            if (!empty($data['check_in']) && Carbon::parse($data['check_in'])->toDateString() !== $date->toDateString()) { $this->fail('Giờ vào phải thuộc ngày chấm công.'); }
            if ($data['overtime_minutes'] > 0) {
                if (empty($data['check_in']) || empty($data['check_out'])) { $this->fail('Xác nhận OT cần đủ giờ vào và giờ ra.'); }
                $in = Carbon::parse($data['check_in']); $out = Carbon::parse($data['check_out']);
                if ($in->toDateString() !== $out->toDateString() || $in->format('H:i') < '06:00' || $out->format('H:i:s') > '22:00:00') {
                    $this->fail('OT qua đêm cần công thức và dữ liệu riêng; phiên bản này chỉ xử lý OT ban ngày.');
                }
                if ($data['overtime_minutes'] > $in->diffInMinutes($out)) { $this->fail('Phút OT vượt tổng thời gian đã ghi nhận.'); }
                $working = in_array($date->dayOfWeekIso, $schedule->work_days, true);
                if (!$working && $data['overtime_type'] === 'weekday') {
                    $this->fail('Loại ngày OT không khớp lịch làm việc. Ngày lễ/nghỉ hưởng lương phải được HR xác nhận riêng.');
                }
                if (!in_array($data['overtime_status'], ['approved', 'rejected'], true)) { $this->fail('HR phải duyệt hoặc từ chối phần OT.'); }
            } else {
                $data['overtime_status'] = 'none'; $data['overtime_type'] = null;
            }
            $old = [];
            if ($attendance) { $attendance = Attendance::lockForUpdate()->findOrFail($attendance->id); $old = $attendance->getAttributes(); }
            elseif (Attendance::where('employee_id', $employee->id)->whereDate('work_date', $date)->exists()) { $this->fail('Đã có bản ghi ngày này. Hãy sửa bản ghi hiện tại.'); }
            $data['work_units'] = number_format($data['regular_minutes'] / $schedule->standard_minutes, 4, '.', '');
            $data['overtime_hours'] = number_format($data['overtime_minutes'] / 60, 2, '.', '');
            $data['overtime_approved_by'] = $data['overtime_status'] === 'approved' ? $actor->id : null;
            $data['status'] = 'adjusted'; $data['adjusted_by'] = $actor->id;
            if ($attendance) { $attendance->update($data); } else { $attendance = Attendance::create($data); }
            AuditService::record('ATTENDANCE_ADJUSTED', $attendance, $old);

            // The period row is locked above. Published/approved periods never reach this branch.
            if ($period && in_array($period->status, ['draft', 'rejected'], true)) {
                $detail = $period->details()->where('employee_id', $employee->id)->lockForUpdate()->first();
                if ($detail) {
                    $oldSnapshot = $detail->calculation_snapshot;
                    $snapshot = $oldSnapshot ?? [];
                    $snapshot['requires_recalculation'] = true;
                    $snapshot['attendance_changed_at'] = now()->toIso8601String();
                    $snapshot['attendance_changed_by'] = $actor->id;
                    $snapshot['changed_attendance_id'] = $attendance->id;
                    $detail->update(['calculation_snapshot' => $snapshot]);
                    AuditService::record('PAYROLL_RECALCULATION_REQUIRED', $detail,
                        ['calculation_snapshot' => $oldSnapshot], ['calculation_snapshot' => $snapshot]);
                }
            }
        }, 3);
    }

    private function lockPeriod(Carbon $date): ?PayrollPeriod
    {
        $period = PayrollPeriod::where('year', $date->year)->where('month', $date->month)->lockForUpdate()->first();
        if ($period && in_array($period->status, ['pending_approval', 'approved', 'published'], true)) {
            $this->fail('Chấm công tháng này đã khóa theo kỳ lương. Kỳ đang trình duyệt cần được từ chối trước khi HR sửa.');
        }
        return $period;
    }

    private function assertAttendanceAccess(User $user, Employee $employee): void
    {
        if ($user->status !== 'active') { $this->fail('Tài khoản không được phép chấm công.'); }
        if ($employee->employment_status !== 'working' || !$employee->workSchedule || $employee->workSchedule->status !== 'active') {
            $this->fail('Nhân viên và lịch làm việc phải đang hoạt động.');
        }
        if ($employee->workSchedule->start_time >= $employee->workSchedule->end_time) {
            $this->fail('Lịch có ca qua đêm cần quy tắc chấm công riêng. Vui lòng liên hệ HR.');
        }
    }

    private function fail(string $message): never { throw ValidationException::withMessages(['attendance' => $message]); }
}
