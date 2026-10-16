<?php
namespace App\Http\Controllers;

use App\Http\Requests\{StorePayrollPeriodRequest, CalculatePayrollRequest, RejectApprovalRequest};

use App\Models\{PayrollPeriod, WorkSchedule};
use App\Services\{AuditService, PayrollService, WorkTimeCalculator};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayrollPeriodController extends Controller
{
    public function index()
    {
        $periods = PayrollPeriod::withCount('details')->orderByDesc('year')->orderByDesc('month')->paginate(15);
        return view('admin.payroll.index', compact('periods'));
    }
    public function create() { return view('admin.payroll.create', ['schedules' => WorkSchedule::where('status', 'active')->get()]); }
    public function store(StorePayrollPeriodRequest $request)
    {
        $data = $request->validated();
        $schedule = WorkSchedule::findOrFail($data['work_schedule_id']);
        $data['standard_work_days'] = WorkTimeCalculator::standardDays((int)$data['year'], (int)$data['month'], $schedule->work_days);
        unset($data['work_schedule_id']);
        $period = DB::transaction(function () use ($request, $data) {
            $period = PayrollPeriod::create([...$data, 'status' => 'draft', 'created_by' => $request->user()->id]);
            AuditService::record('PAYROLL_CREATED', $period);
            return $period;
        });
        return redirect()->route('payrolls.show', $period)->with('success', 'Đã tạo kỳ lương.');
    }
    public function show(PayrollPeriod $payroll, PayrollService $service)
    {
        $payroll->load(['details.employee.department', 'details.employee.position', 'details.contract', 'creator.employee', 'approver.employee']);
        $editable = in_array($payroll->status, ['draft', 'rejected'], true);
        $employees = $editable ? $service->candidates($payroll)->get() : collect();
        return view('admin.payroll.show', ['period' => $payroll, 'employees' => $employees, 'overtimeRows' => $editable ? $service->overtimeInputs($payroll) : collect()]);
    }
    public function calculate(CalculatePayrollRequest $request, PayrollPeriod $payroll, PayrollService $service)
    {
        $data = $request->validated();
        $service->calculate($request->user(), $payroll, $data['adjustments'], $data['overtime'] ?? []);
        return back()->with('success', 'Đã tính lương và lưu snapshot.');
    }
    public function submit(Request $request, PayrollPeriod $payroll, PayrollService $service)
    {
        $service->transition($request->user(), $payroll, 'submit');
        return back()->with('success', 'Đã trình bảng lương.');
    }
    public function approve(Request $request, PayrollPeriod $payroll, PayrollService $service)
    {
        $service->transition($request->user(), $payroll, 'approve');
        return back()->with('success', 'Đã duyệt và tự động công bố bảng lương.');
    }
    public function reject(RejectApprovalRequest $request, PayrollPeriod $payroll, PayrollService $service)
    {
        $data = $request->validated();
        $service->transition($request->user(), $payroll, 'reject', $data['rejection_reason']);
        return back()->with('success', 'Đã từ chối bảng lương.');
    }
}
