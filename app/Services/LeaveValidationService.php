<?php

namespace App\Services;

use App\Models\{Employee, LeaveRequest};
use Carbon\{Carbon, CarbonInterface, CarbonPeriod};
use Illuminate\Validation\ValidationException;

class LeaveValidationService
{
    /**
     * Tính tổng số ngày nghỉ thực tế từ start_date đến end_date,
     * tự động loại trừ Thứ Bảy và Chủ Nhật (dùng CarbonPeriod).
     */
    public function calculateTotalDays(CarbonInterface|string $startDate, CarbonInterface|string $endDate): float
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->startOfDay();

        if ($end->lt($start)) {
            return 0.0;
        }

        $period = CarbonPeriod::create($start, $end);
        $weekdays = 0;

        foreach ($period as $date) {
            if ($date->isWeekday()) {
                $weekdays++;
            }
        }

        return (float) $weekdays;
    }

    /**
     * Tính động quỹ phép năm của nhân viên trong năm:
     * Được hưởng (annual_leave_days, mặc định 12) - Đã dùng (approved annual) = Còn lại.
     */
    public function getLeaveBalance(Employee $employee, ?int $year = null): array
    {
        $year = $year ?: (int) now()->year;
        $entitled = (float) ($employee->annual_leave_days ?? 12);

        $used = (float) LeaveRequest::where('employee_id', $employee->id)
            ->where('leave_type', 'annual')
            ->where('status', 'approved')
            ->whereYear('start_date', $year)
            ->sum('total_days');

        $remaining = max(0.0, $entitled - $used);

        return [
            'entitled' => $entitled,
            'used' => $used,
            'remaining' => $remaining,
        ];
    }

    /**
     * Kiểm tra 5 quy tắc nghiệp vụ khi nhân viên đăng ký nghỉ phép thường (is_retroactive = false).
     */
    public function validateStandardLeave(
        Employee $employee,
        string $leaveType,
        string $startDateStr,
        string $endDateStr,
        ?CarbonInterface $createdAt = null
    ): float {
        $now = $createdAt ? Carbon::parse($createdAt) : now();
        $startDate = Carbon::parse($startDateStr)->startOfDay();
        $endDate = Carbon::parse($endDateStr)->startOfDay();

        // Quy tắc 1: Chỉ được tạo đơn từ Thứ Hai đến Thứ Sáu
        if (!$now->isWeekday()) {
            throw ValidationException::withMessages([
                'start_date' => 'Chỉ được phép tạo đơn đăng ký nghỉ phép từ Thứ Hai đến Thứ Sáu (không nhận đơn vào Thứ Bảy và Chủ Nhật).',
            ]);
        }

        // Tính tổng số ngày làm việc (loại trừ Thứ Bảy, Chủ Nhật)
        $totalDays = $this->calculateTotalDays($startDate, $endDate);
        if ($totalDays <= 0) {
            throw ValidationException::withMessages([
                'start_date' => 'Khoảng thời gian chọn chỉ rơi vào Thứ Bảy/Chủ Nhật. Vui lòng chọn ngày làm việc từ Thứ Hai đến Thứ Sáu.',
            ]);
        }

        // Kiểm tra trùng thời gian với đơn đang chờ hoặc đã duyệt của chính nhân viên
        $this->ensureNoOverlap($employee->id, $startDate, $endDate);

        // Quy tắc 2: Thời gian báo trước 7 ngày đối với annual, personal, unpaid (ngoại lệ: sick)
        if (in_array($leaveType, ['annual', 'personal', 'unpaid'], true)) {
            $daysNotice = $now->copy()->startOfDay()->diffInDays($startDate, false);
            if ($daysNotice < 7) {
                throw ValidationException::withMessages([
                    'start_date' => 'Đơn nghỉ phép năm, việc riêng hoặc nghỉ không lương phải được đăng ký trước ngày bắt đầu nghỉ ít nhất 7 ngày.',
                ]);
            }
        }

        // Quy tắc 3: Giới hạn tối đa 2 đơn / tháng (không tính đơn đã cancelled hoặc rejected)
        $monthlyCount = LeaveRequest::where('employee_id', $employee->id)
            ->whereYear('start_date', $startDate->year)
            ->whereMonth('start_date', $startDate->month)
            ->whereNotIn('status', ['cancelled', 'rejected'])
            ->count();

        if ($monthlyCount >= 2) {
            throw ValidationException::withMessages([
                'start_date' => 'Mỗi nhân viên chỉ được đăng ký tối đa 2 đơn nghỉ phép trong một tháng.',
            ]);
        }

        // Quy tắc 4: Quỹ phép năm (12 ngày/năm) đối với leave_type === 'annual'
        if ($leaveType === 'annual') {
            $balance = $this->getLeaveBalance($employee, $startDate->year);
            if ($totalDays > $balance['remaining']) {
                throw ValidationException::withMessages([
                    'leave_type' => "Số ngày nghỉ phép năm yêu cầu ({$totalDays} ngày) vượt quá số ngày phép năm còn lại của bạn ({$balance['remaining']} ngày).",
                ]);
            }
        }

        // Quy tắc 5: Giới hạn tối đa 2 người / phòng ban / ngày đối với leave_type === 'annual'
        if ($leaveType === 'annual' && $employee->department_id) {
            $period = CarbonPeriod::create($startDate, $endDate);
            foreach ($period as $day) {
                if (!$day->isWeekday()) {
                    continue;
                }

                $dateString = $day->toDateString();
                $deptLeaveCount = LeaveRequest::where('leave_type', 'annual')
                    ->whereIn('status', ['approved', 'pending'])
                    ->where('employee_id', '!=', $employee->id)
                    ->whereDate('start_date', '<=', $dateString)
                    ->whereDate('end_date', '>=', $dateString)
                    ->whereHas('employee', fn ($q) => $q->where('department_id', $employee->department_id))
                    ->distinct('employee_id')
                    ->count('employee_id');

                if ($deptLeaveCount >= 2) {
                    throw ValidationException::withMessages([
                        'start_date' => 'Ngày ' . $day->format('d/m/Y') . ' phòng ban của bạn đã có đủ 2 nhân viên nghỉ phép năm. Vui lòng chọn ngày khác.',
                    ]);
                }
            }
        }

        return $totalDays;
    }

    /**
     * Kiểm tra khi HR hoặc Director nhập Nghỉ bù / Nghỉ phép trong quá khứ (is_retroactive = true).
     * Bỏ qua quy tắc báo trước 7 ngày và giới hạn 2 đơn/tháng.
     */
    public function validateRetroactiveLeave(
        Employee $employee,
        string $leaveType,
        string $startDateStr,
        string $endDateStr
    ): float {
        $startDate = Carbon::parse($startDateStr)->startOfDay();
        $endDate = Carbon::parse($endDateStr)->startOfDay();

        $totalDays = $this->calculateTotalDays($startDate, $endDate);
        if ($totalDays <= 0) {
            throw ValidationException::withMessages([
                'start_date' => 'Khoảng thời gian chọn chỉ rơi vào Thứ Bảy/Chủ Nhật. Vui lòng chọn ngày làm việc.',
            ]);
        }

        $this->ensureNoOverlap($employee->id, $startDate, $endDate);

        if ($leaveType === 'annual') {
            $balance = $this->getLeaveBalance($employee, $startDate->year);
            if ($totalDays > $balance['remaining']) {
                throw ValidationException::withMessages([
                    'leave_type' => "Số ngày nghỉ phép năm ({$totalDays} ngày) vượt quá số ngày phép còn lại của nhân viên ({$balance['remaining']} ngày).",
                ]);
            }
        }

        return $totalDays;
    }

    private function ensureNoOverlap(int $employeeId, CarbonInterface $startDate, CarbonInterface $endDate): void
    {
        $overlap = LeaveRequest::where('employee_id', $employeeId)
            ->whereIn('status', ['pending', 'approved'])
            ->whereDate('start_date', '<=', $endDate->toDateString())
            ->whereDate('end_date', '>=', $startDate->toDateString())
            ->exists();

        if ($overlap) {
            throw ValidationException::withMessages([
                'start_date' => 'Khoảng thời gian này trùng với một đơn nghỉ phép đang chờ duyệt hoặc đã được phê duyệt.',
            ]);
        }
    }
}
