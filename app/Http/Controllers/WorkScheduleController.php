<?php
namespace App\Http\Controllers;

use App\Http\Requests\SaveWorkScheduleRequest;

use App\Models\WorkSchedule;
use App\Services\AuditService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WorkScheduleController extends Controller
{
    public function index()
    {
        return view('admin.schedule.index', ['schedules' => WorkSchedule::withCount('employees')->orderBy('id')->get()]);
    }
    public function create() { return view('admin.schedule.form'); }
    public function edit(WorkSchedule $schedule) { return view('admin.schedule.form', compact('schedule')); }
    public function store(SaveWorkScheduleRequest $request)
    {
        $data = $request->payload();
        DB::transaction(function () use ($data) {
            $schedule = WorkSchedule::create($data);
            AuditService::record('WORK_SCHEDULE_CREATED', $schedule);
        });
        return redirect()->route('schedules.index')->with('success', 'Đã tạo lịch làm việc.');
    }
    public function update(SaveWorkScheduleRequest $request, WorkSchedule $schedule)
    {
        $data = $request->payload();
        DB::transaction(function () use ($schedule, $data) {
            $schedule = WorkSchedule::lockForUpdate()->findOrFail($schedule->id);
            $old = $schedule->getAttributes();
            $schedule->fill($data);
            if ($schedule->employees()->exists() && $schedule->isDirty(['start_time','end_time','break_start','break_end','work_days','standard_minutes','late_grace_minutes'])) {
                throw ValidationException::withMessages(['schedule' => 'Lịch đã được gán cho nhân viên. Tạo lịch mới để tránh đổi dữ liệu công lịch sử.']);
            }
            $schedule->save();
            AuditService::record('WORK_SCHEDULE_UPDATED', $schedule, $old);
        });
        return redirect()->route('schedules.index')->with('success', 'Đã lưu lịch làm việc.');
    }
}
