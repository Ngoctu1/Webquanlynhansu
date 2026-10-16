<?php
namespace App\Http\Controllers;

use App\Http\Requests\{FilterAttendancesRequest, SaveAttendanceRequest};

use App\Models\{Attendance, Employee};
use App\Services\AttendanceService;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(FilterAttendancesRequest $request)
    {
        $filters = $request->validated();
        $attendances = Attendance::with('employee')
            ->when($filters['employee_id'] ?? null, fn ($q, $id) => $q->where('employee_id', $id))
            ->when($filters['from'] ?? null, fn ($q, $date) => $q->whereDate('work_date', '>=', $date))
            ->when($filters['to'] ?? null, fn ($q, $date) => $q->whereDate('work_date', '<=', $date))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->orderByDesc('work_date')->orderByDesc('id')->paginate(20)->withQueryString();
        $employees = Employee::orderBy('employee_code')->get();
        $missingDates = [];
        if (!empty($filters['employee_id'])) {
            $employee = Employee::with('workSchedule')->findOrFail($filters['employee_id']);
            $from = Carbon::parse($filters['from'] ?? now()->startOfMonth()->toDateString());
            $to = Carbon::parse($filters['to'] ?? today()->toDateString())->min(today());
            if ($from->diffInDays($to, true) > 62) {
                throw \Illuminate\Validation\ValidationException::withMessages(['from' => 'Chọn tối đa 62 ngày để kiểm tra ngày thiếu công.']);
            }
            $recorded = $employee->attendances()->whereBetween('work_date', [$from->toDateString(), $to->toDateString()])
                ->pluck('work_date')->map(fn ($date) => substr((string)$date, 0, 10))->all();
            for ($day = $from->copy(); $day->lte($to); $day->addDay()) {
                if (in_array($day->dayOfWeekIso, $employee->workSchedule->work_days, true)
                    && (!$employee->hire_date || $day->gte($employee->hire_date))
                    && !in_array($day->toDateString(), $recorded, true)) { $missingDates[] = $day->format('d/m/Y'); }
            }
        }
        return view('admin.attendance.index', compact('attendances', 'employees', 'missingDates'));
    }
    public function create() { return view('admin.attendance.create', ['employees' => Employee::orderBy('employee_code')->get()]); }
    public function store(SaveAttendanceRequest $request, AttendanceService $service)
    {
        $service->adjust($request->user(), $request->payload());
        return redirect()->route('attendances.index')->with('success', 'Đã ghi nhận công và lý do điều chỉnh. Nếu kỳ lương đã được tính, HR cần tính lại trước khi trình duyệt.');
    }
    public function edit(Attendance $attendance) { return view('admin.attendance.edit', compact('attendance')); }
    public function update(SaveAttendanceRequest $request, Attendance $attendance, AttendanceService $service)
    {
        $service->adjust($request->user(), $request->payload(), $attendance);
        return redirect()->route('attendances.index')->with('success', 'Đã điều chỉnh chấm công. Nếu kỳ lương đã được tính, HR cần tính lại trước khi trình duyệt.');
    }
    public function mine(Request $request)
    {
        $attendanceNow = now();
        $todayAttendance = Attendance::where('employee_id', $request->user()->employee_id)
            ->whereDate('work_date', $attendanceNow->toDateString())->first();
        $missingCheckouts = Attendance::where('employee_id', $request->user()->employee_id)
            ->whereDate('work_date', '<', $attendanceNow->toDateString())
            ->whereNotNull('check_in')->whereNull('check_out')->where('status', 'checked_in')
            ->orderBy('work_date')->get(['id', 'work_date']);

        return view('user.attendance.index', compact('attendanceNow', 'todayAttendance', 'missingCheckouts'));
    }
    public function history(Request $request)
    {
        $employeeId = $request->user()->employee_id;
        abort_unless($employeeId, 403, 'Tài khoản chưa được liên kết với nhân viên.');

        $filters = $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => array_merge(['nullable', 'date_format:Y-m-d'], $request->filled('from') ? ['after_or_equal:from'] : []),
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year' => ['nullable', 'required_with:month', 'integer', 'between:1,9999'],
            'status' => ['nullable', 'in:checked_in,completed,adjusted'],
            'page' => ['nullable', 'integer', 'min:1'],
            'employee_id' => ['prohibited'],
        ], [
            'from.date_format' => 'Từ ngày phải là ngày hợp lệ theo định dạng năm-tháng-ngày.',
            'to.date_format' => 'Đến ngày phải là ngày hợp lệ theo định dạng năm-tháng-ngày.',
            'to.after_or_equal' => 'Đến ngày phải bằng hoặc sau Từ ngày.',
            'month.integer' => 'Tháng phải là số nguyên.',
            'month.between' => 'Tháng phải nằm trong khoảng từ 1 đến 12.',
            'year.required_with' => 'Vui lòng nhập năm khi lọc theo tháng.',
            'year.integer' => 'Năm phải là số nguyên.',
            'year.between' => 'Năm phải nằm trong khoảng từ 1 đến 9999.',
            'status.in' => 'Trạng thái chấm công không hợp lệ.',
            'page.integer' => 'Số trang không hợp lệ.',
            'page.min' => 'Số trang phải từ 1 trở lên.',
            'employee_id.prohibited' => 'Bạn chỉ có thể xem lịch sử chấm công của chính mình.',
        ]);

        $attendanceNow = now();
        $attendances = Attendance::where('employee_id', $employeeId)
            ->when($filters['from'] ?? null, fn ($q, $date) => $q->whereDate('work_date', '>=', $date))
            ->when($filters['to'] ?? null, fn ($q, $date) => $q->whereDate('work_date', '<=', $date))
            ->when($filters['month'] ?? null, fn ($q, $month) => $q->whereMonth('work_date', $month))
            ->when($filters['year'] ?? null, fn ($q, $year) => $q->whereYear('work_date', $year))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->orderByDesc('work_date')->orderByDesc('id')->paginate(10)->withQueryString();

        return view('user.attendance.history', compact('attendances', 'attendanceNow', 'filters'));
    }
    public function checkIn(Request $request, AttendanceService $service)
    {
        $service->checkIn($request->user());
        return back()->with('success', 'Đã check-in theo giờ máy chủ.');
    }
    public function checkOut(Request $request, AttendanceService $service)
    {
        $service->checkOut($request->user());
        return back()->with('success', 'Đã check-out. HR sẽ xác nhận ngày công theo quy định.');
    }
}
