<?php

namespace App\Console\Commands;

use App\Models\LeaveRequest;
use App\Services\AuditLogger;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ProcessOverdueLeaves extends Command
{
    protected $signature = 'app:process-overdue-leaves';

    protected $description = 'Tự động chuyển các đơn nghỉ phép pending đã tới ngày bắt đầu nghỉ (start_date <= today) sang trạng thái expired và ghi nhận Audit Log.';

    public function handle(): int
    {
        $today = today()->toDateString();
        $expiredCount = 0;

        DB::transaction(function () use ($today, &$expiredCount) {
            $overdueLeaves = LeaveRequest::where('status', 'pending')
                ->whereDate('start_date', '<=', $today)
                ->lockForUpdate()
                ->get();

            foreach ($overdueLeaves as $leave) {
                $oldStatus = $leave->status;
                $leave->update([
                    'status' => 'expired',
                ]);

                AuditLogger::log(
                    'expire_overdue_leave',
                    'leave_request',
                    $leave->id,
                    ['status' => $oldStatus],
                    ['status' => 'expired', 'start_date' => $leave->start_date?->toDateString()]
                );

                $expiredCount++;
            }
        });

        $stalePendingCount = LeaveRequest::where('status', 'pending')
            ->where('created_at', '<=', now()->subDays(2))
            ->count();

        $this->info("Đã chuyển {$expiredCount} đơn nghỉ phép quá hạn sang trạng thái 'expired'.");
        if ($stalePendingCount > 0) {
            $this->warn("Cảnh báo: Hiện có {$stalePendingCount} đơn nghỉ phép chờ duyệt đã quá 2 ngày chưa được xử lý.");
        }

        return self::SUCCESS;
    }
}
