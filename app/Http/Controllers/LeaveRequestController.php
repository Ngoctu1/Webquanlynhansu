<?php

namespace App\Http\Controllers;

use App\Http\Requests\{FilterLeaveRequestsRequest, ReviewLeaveRequest, StoreLeaveRequest};
use App\Models\{Employee, LeaveRequest, Role};
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LeaveRequestController extends Controller
{
    public function mine(Request $request)
    {
        $employeeId = $request->user()->employee_id;
        abort_unless($employeeId, 403, 'Tài khoản chưa được liên kết với nhân viên.');
        $leaveRequests = LeaveRequest::where('employee_id', $employeeId)->latest('id')->paginate(15);
        return view('user.leave.index', compact('leaveRequests'));
    }

    public function store(StoreLeaveRequest $request)
    {
        $data = $request->validated();
        DB::transaction(function () use ($request, $data) {
            $employee = Employee::lockForUpdate()->findOrFail($request->user()->employee_id);
            $overlap = LeaveRequest::where('employee_id', $employee->id)->whereIn('status', ['pending', 'approved'])
                ->whereDate('start_date', '<=', $data['end_date'])->whereDate('end_date', '>=', $data['start_date'])
                ->lockForUpdate()->exists();
            if ($overlap) {
                throw ValidationException::withMessages(['start_date' => 'Khoảng thời gian này trùng với đơn nghỉ đang chờ duyệt hoặc đã được duyệt.']);
            }
            $leaveRequest = LeaveRequest::create([...$data, 'employee_id' => $employee->id, 'status' => 'pending']);
            AuditService::record('LEAVE_REQUEST_CREATED', $leaveRequest);
        }, 3);
        return redirect()->route('user.leave-requests.index')->with('success', 'Đã gửi đơn nghỉ phép để HR xét duyệt.');
    }

    public function cancel(Request $request, LeaveRequest $leaveRequest)
    {
        abort_unless((int) $leaveRequest->employee_id === (int) $request->user()->employee_id, 403);
        DB::transaction(function () use ($request, $leaveRequest) {
            $leaveRequest = LeaveRequest::lockForUpdate()->findOrFail($leaveRequest->id);
            if ($leaveRequest->status !== 'pending') {
                throw ValidationException::withMessages(['leave_request' => 'Chỉ có thể hủy đơn đang chờ duyệt.']);
            }
            $old = $leaveRequest->getAttributes();
            $leaveRequest->update(['status' => 'cancelled', 'reviewed_by' => null, 'reviewed_at' => null, 'rejection_reason' => null]);
            AuditService::record('LEAVE_REQUEST_CANCELLED', $leaveRequest, $old);
        }, 3);
        return back()->with('success', 'Đã hủy đơn nghỉ phép.');
    }

    public function index(FilterLeaveRequestsRequest $request)
    {
        $filters = $request->validated();
        $leaveRequests = LeaveRequest::with(['employee.department', 'reviewer.employee'])
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['q'] ?? null, fn ($query, $q) => $query->whereHas('employee', fn ($employee) => $employee
                ->where('full_name', 'like', '%'.$q.'%')->orWhere('employee_code', 'like', '%'.$q.'%')))
            ->latest('id')->paginate(20)->withQueryString();
        return view('admin.leave.index', compact('leaveRequests', 'filters'));
    }

    public function show(LeaveRequest $leaveRequest)
    {
        abort_unless(request()->user()->hasRole(Role::HR), 403);
        $leaveRequest->load(['employee.department', 'reviewer.employee']);
        return view('admin.leave.show', compact('leaveRequest'));
    }

    public function approve(Request $request, LeaveRequest $leaveRequest)
    {
        $this->review($request, $leaveRequest, 'approved');
        return back()->with('success', 'Đã duyệt đơn nghỉ phép.');
    }

    public function reject(ReviewLeaveRequest $request, LeaveRequest $leaveRequest)
    {
        $this->review($request, $leaveRequest, 'rejected', $request->validated('rejection_reason'));
        return back()->with('success', 'Đã từ chối đơn nghỉ phép.');
    }

    private function review(Request $request, LeaveRequest $leaveRequest, string $status, ?string $reason = null): void
    {
        abort_unless($request->user()->hasRole(Role::HR), 403);
        DB::transaction(function () use ($request, $leaveRequest, $status, $reason) {
            $leaveRequest = LeaveRequest::lockForUpdate()->findOrFail($leaveRequest->id);
            if ($leaveRequest->status !== 'pending') {
                throw ValidationException::withMessages(['leave_request' => 'Đơn này không còn ở trạng thái chờ duyệt.']);
            }
            $old = $leaveRequest->getAttributes();
            $leaveRequest->update(['status' => $status, 'reviewed_by' => $request->user()->id, 'reviewed_at' => now(),
                'rejection_reason' => $status === 'rejected' ? trim((string) $reason) : null]);
            AuditService::record($status === 'approved' ? 'LEAVE_REQUEST_APPROVED' : 'LEAVE_REQUEST_REJECTED', $leaveRequest, $old);
        }, 3);
    }
}
