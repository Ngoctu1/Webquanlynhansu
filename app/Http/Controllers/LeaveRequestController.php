<?php

namespace App\Http\Controllers;

use App\Http\Requests\{FilterLeaveRequestsRequest, ReviewLeaveRequest, StoreLeaveRequest};
use App\Models\{Department, Employee, LeaveRequest};
use App\Services\{AuditLogger, LeaveValidationService};
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Storage};
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeaveRequestController extends Controller
{
    public function mine(Request $request, LeaveValidationService $validationService)
    {
        $user = $request->user();
        abort_if($user->isAdmin() || !$user->employee_id, 403, 'Tài khoản Admin không có hồ sơ nhân viên để đăng ký nghỉ phép.');

        $employee = Employee::with(['department', 'position'])->findOrFail($user->employee_id);
        $balance = $validationService->getLeaveBalance($employee);

        $leaveRequests = LeaveRequest::where('employee_id', $employee->id)
            ->latest('id')
            ->paginate(15);

        return view('user.leave.index', compact('leaveRequests', 'employee', 'balance'));
    }

    public function store(StoreLeaveRequest $request, LeaveValidationService $validationService)
    {
        $user = $request->user();
        $this->authorize('create', LeaveRequest::class);

        $data = $request->validated();

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $ext = strtolower($request->file('attachment')->getClientOriginalExtension());
            $uuidFilename = (string) Str::uuid() . '.' . $ext;
            $attachmentPath = $request->file('attachment')->storeAs('leaves', $uuidFilename, 'local');
        }

        try {
            // Đặc quyền của Director: Tự nghỉ không cần qua luồng chờ duyệt, ghi nhận trực tiếp & lưu Audit Log
            if ($user->isDirector()) {
                $leaveRequest = DB::transaction(function () use ($user, $data, $attachmentPath, $validationService) {
                    $employee = Employee::lockForUpdate()->findOrFail($user->employee_id);
                    $totalDays = $validationService->calculateTotalDays($data['start_date'], $data['end_date']);
                    if ($totalDays <= 0) {
                        throw ValidationException::withMessages([
                            'start_date' => 'Khoảng thời gian chọn chỉ rơi vào cuối tuần. Vui lòng chọn ngày làm việc.',
                        ]);
                    }

                    $leave = LeaveRequest::create([
                        'employee_id' => $employee->id,
                        'leave_type' => $data['leave_type'],
                        'start_date' => $data['start_date'],
                        'end_date' => $data['end_date'],
                        'total_days' => $totalDays,
                        'reason' => $data['reason'],
                        'attachment_path' => $attachmentPath,
                        'is_retroactive' => false,
                        'created_by' => $user->id,
                        'status' => 'approved',
                        'reviewed_by' => $user->id,
                        'reviewed_at' => now(),
                    ]);

                    AuditLogger::log('director_self_leave', 'leave_request', $leave->id, null, $leave->toArray());

                    return $leave;
                });

                return redirect()
                    ->route('user.leave-requests.index')
                    ->with('success', 'Đã ghi nhận lịch nghỉ của Giám đốc và lưu vào nhật ký hệ thống.');
            }

            // Nhân viên thường & HR: Kiểm tra 5 quy tắc trong LeaveValidationService
            DB::transaction(function () use ($user, $data, $attachmentPath, $validationService) {
                $employee = Employee::lockForUpdate()->findOrFail($user->employee_id);

                $totalDays = $validationService->validateStandardLeave(
                    $employee,
                    $data['leave_type'],
                    $data['start_date'],
                    $data['end_date']
                );

                $leaveRequest = LeaveRequest::create([
                    'employee_id' => $employee->id,
                    'leave_type' => $data['leave_type'],
                    'start_date' => $data['start_date'],
                    'end_date' => $data['end_date'],
                    'total_days' => $totalDays,
                    'reason' => $data['reason'],
                    'attachment_path' => $attachmentPath,
                    'is_retroactive' => false,
                    'created_by' => $user->id,
                    'status' => 'pending',
                ]);

                AuditLogger::log('create', 'leave_request', $leaveRequest->id, null, $leaveRequest->toArray());
            }, 3);
        } catch (\Throwable $e) {
            if ($attachmentPath) {
                Storage::disk('local')->delete($attachmentPath);
            }
            throw $e;
        }

        $targetApprover = $user->isHR() ? 'Giám đốc' : 'Phòng Nhân sự (HR) / Giám đốc';

        return redirect()
            ->route('user.leave-requests.index')
            ->with('success', "Đã gửi đơn đăng ký nghỉ phép chờ {$targetApprover} phê duyệt.");
    }

    /**
     * Chức năng Nút "Nghỉ bù" - Nhập nghỉ phép trong quá khứ (Chỉ HR và Director).
     */
    public function storeRetroactive(Request $request, LeaveValidationService $validationService)
    {
        $user = $request->user();
        $this->authorize('createRetroactive', LeaveRequest::class);

        $data = $request->validate([
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'leave_type' => ['required', Rule::in(['annual', 'sick', 'personal', 'unpaid'])],
            'start_date' => ['required', 'date', 'before_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date', 'before_or_equal:today'],
            'reason' => ['required', 'string', 'max:5000'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ], [
            'start_date.before_or_equal' => 'Chức năng Nghỉ bù chỉ áp dụng cho ngày nghỉ trong quá khứ hoặc hôm nay.',
            'end_date.before_or_equal' => 'Ngày kết thúc nghỉ bù không được vượt quá ngày hôm nay.',
            'end_date.after_or_equal' => 'Ngày kết thúc phải sau hoặc bằng ngày bắt đầu.',
            'reason.required' => 'Bắt buộc nhập lý do khi ghi nhận nghỉ bù.',
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $ext = strtolower($request->file('attachment')->getClientOriginalExtension());
            $uuidFilename = (string) Str::uuid() . '.' . $ext;
            $attachmentPath = $request->file('attachment')->storeAs('leaves', $uuidFilename, 'local');
        }

        try {
            $applyDirectly = $user->can('canApplyDirectly', LeaveRequest::class);

            DB::transaction(function () use ($user, $data, $attachmentPath, $validationService, $applyDirectly) {
                $employee = Employee::visibleTo($user)
                    ->lockForUpdate()
                    ->findOrFail($data['employee_id']);

                if ($employee->isAdminOrDirector()) {
                    abort(403, 'Không tạo đơn nghỉ bù cho Admin hoặc Giám đốc.');
                }

                $totalDays = $validationService->validateRetroactiveLeave(
                    $employee,
                    $data['leave_type'],
                    $data['start_date'],
                    $data['end_date']
                );

                $leaveRequest = LeaveRequest::create([
                    'employee_id' => $employee->id,
                    'leave_type' => $data['leave_type'],
                    'start_date' => $data['start_date'],
                    'end_date' => $data['end_date'],
                    'total_days' => $totalDays,
                    'reason' => $data['reason'],
                    'attachment_path' => $attachmentPath,
                    'is_retroactive' => true,
                    'created_by' => $user->id,
                    'status' => $applyDirectly ? 'approved' : 'pending',
                    'reviewed_by' => $applyDirectly ? $user->id : null,
                    'reviewed_at' => $applyDirectly ? now() : null,
                ]);

                AuditLogger::log('create_retroactive', 'leave_request', $leaveRequest->id, null, $leaveRequest->toArray());
            }, 3);
        } catch (\Throwable $e) {
            if ($attachmentPath) {
                Storage::disk('local')->delete($attachmentPath);
            }
            throw $e;
        }

        $msg = $user->isDirector()
            ? 'Đã ghi nhận và phê duyệt đơn Nghỉ bù thành công.'
            : 'Đã tạo đơn Nghỉ bù trong quá khứ và chuyển chờ Giám đốc phê duyệt.';

        return redirect()->route('leave-requests.index', ['week' => 'all'])->with('success', $msg);
    }

    public function cancel(Request $request, LeaveRequest $leaveRequest)
    {
        $this->authorize('cancel', $leaveRequest);

        DB::transaction(function () use ($leaveRequest) {
            $leaveRequest = LeaveRequest::lockForUpdate()->findOrFail($leaveRequest->id);
            if ($leaveRequest->status !== 'pending') {
                throw ValidationException::withMessages(['leave_request' => 'Chỉ có thể hủy đơn khi đang ở trạng thái chờ duyệt (pending).']);
            }

            $oldValues = ['status' => $leaveRequest->status];
            $leaveRequest->update([
                'status' => 'cancelled',
                'reviewed_by' => null,
                'reviewed_at' => null,
                'rejection_reason' => null,
            ]);

            AuditLogger::log('cancel', 'leave_request', $leaveRequest->id, $oldValues, ['status' => 'cancelled']);
        }, 3);

        return back()->with('success', 'Đã hủy đơn xin nghỉ phép thành công.');
    }

    public function index(FilterLeaveRequestsRequest $request)
    {
        $this->authorize('viewAny', LeaveRequest::class);

        $user = $request->user();
        $filters = $request->validated();
        $weekFilter = $request->query('week', 'current');
        if (!empty($filters['created_from']) || !empty($filters['created_to'])) {
            $weekFilter = 'all';
        }

        $query = LeaveRequest::query()
            ->whereHas('employee', fn ($q) => $q->visibleTo($user))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['leave_type'] ?? null, fn ($q, $leaveType) => $q->where('leave_type', $leaveType))
            ->when($filters['department_id'] ?? null, fn ($q, $deptId) => $q->whereHas('employee', fn ($eq) => $eq->where('department_id', $deptId)))
            ->when($filters['created_from'] ?? null, fn ($q, $date) => $q->whereDate('start_date', '>=', $date))
            ->when($filters['created_to'] ?? null, fn ($q, $date) => $q->whereDate('end_date', '<=', $date))
            ->when($filters['q'] ?? null, fn ($q, $keyword) => $q->whereHas('employee', fn ($emp) => $emp
                ->where(fn ($sub) => $sub->where('full_name', 'like', '%' . $keyword . '%')
                    ->orWhere('employee_code', 'like', '%' . $keyword . '%'))))
            ->when($weekFilter && $weekFilter !== 'all', function ($q) use ($weekFilter) {
                if ($weekFilter === 'current') {
                    $startOfWeek = now()->startOfWeek()->toDateString();
                    $endOfWeek = now()->endOfWeek()->toDateString();
                } elseif (preg_match('/^(\d{4})-W(\d{2})$/', $weekFilter, $matches)) {
                    $dt = Carbon::now()->setISODate((int) $matches[1], (int) $matches[2]);
                    $startOfWeek = $dt->copy()->startOfWeek()->toDateString();
                    $endOfWeek = $dt->copy()->endOfWeek()->toDateString();
                } else {
                    return;
                }

                $q->where(function ($sub) use ($startOfWeek, $endOfWeek) {
                    $sub->where(function ($overlap) use ($startOfWeek, $endOfWeek) {
                        $overlap->whereDate('start_date', '<=', $endOfWeek)
                            ->whereDate('end_date', '>=', $startOfWeek);
                    })->orWhere('status', 'pending');
                });
            });

        $stats = (clone $query)->selectRaw(
            "COUNT(*) AS total,
             SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending,
             SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) AS approved,
             SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) AS rejected,
             SUM(CASE WHEN status = 'expired' THEN 1 ELSE 0 END) AS expired"
        )->first();

        $leaveRequests = $query->with(['employee.user.role', 'employee.department', 'employee.position', 'creator.employee', 'reviewer.employee'])
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $departments = Department::orderBy('name')->get();
        $eligibleEmployees = Employee::visibleTo($user)
            ->with('user.role')
            ->where('approval_status', 'approved')
            ->orderBy('employee_code')
            ->get()
            ->reject(fn (Employee $emp) => $emp->isAdminOrDirector())
            ->values();

        return view('admin.leave-requests.index', compact(
            'leaveRequests',
            'filters',
            'stats',
            'weekFilter',
            'departments',
            'eligibleEmployees'
        ));
    }

    public function show(LeaveRequest $leaveRequest)
    {
        $leaveRequest->load(['employee.user.role', 'employee.department', 'employee.position', 'creator.employee', 'reviewer.employee']);
        $this->authorize('view', $leaveRequest);

        return view('admin.leave.show', compact('leaveRequest'));
    }

    public function approve(Request $request, LeaveRequest $leaveRequest)
    {
        $this->review($request, $leaveRequest, 'approved');
        return back()->with('success', 'Đã phê duyệt đơn nghỉ phép thành công.');
    }

    public function reject(ReviewLeaveRequest $request, LeaveRequest $leaveRequest)
    {
        $this->review($request, $leaveRequest, 'rejected', $request->validated('rejection_reason'));
        return back()->with('success', 'Đã từ chối đơn nghỉ phép.');
    }

    public function downloadAttachment(LeaveRequest $leaveRequest): StreamedResponse
    {
        $leaveRequest->load('employee.user.role');
        $this->authorize('view', $leaveRequest);

        abort_unless(
            $leaveRequest->attachment_path && Storage::disk('local')->exists($leaveRequest->attachment_path),
            404,
            'Không tìm thấy file đính kèm của đơn nghỉ phép.'
        );

        AuditLogger::log('download_leave_attachment', 'leave_request', $leaveRequest->id, null, [
            'attachment_path' => $leaveRequest->attachment_path,
        ]);

        return Storage::disk('local')->download($leaveRequest->attachment_path);
    }

    private function review(Request $request, LeaveRequest $leaveRequest, string $status, ?string $reason = null): void
    {
        $leaveRequest->load(['employee.user.role', 'creator.role']);
        $this->authorize('approveOrReject', $leaveRequest);

        $actor = $request->user();

        DB::transaction(function () use ($actor, $leaveRequest, $status, $reason) {
            $leaveRequest = LeaveRequest::lockForUpdate()->findOrFail($leaveRequest->id);

            if ($leaveRequest->status !== 'pending') {
                throw ValidationException::withMessages([
                    'leave_request' => 'Đơn nghỉ phép này không còn ở trạng thái chờ duyệt.',
                ]);
            }

            $oldValues = [
                'status' => $leaveRequest->status,
                'reviewed_by' => $leaveRequest->reviewed_by,
                'rejection_reason' => $leaveRequest->rejection_reason,
            ];

            $leaveRequest->update([
                'status' => $status,
                'reviewed_by' => $actor->id,
                'reviewed_at' => now(),
                'rejection_reason' => $status === 'rejected' ? trim((string) $reason) : null,
            ]);

            AuditLogger::log(
                $status === 'approved' ? 'approve' : 'reject',
                'leave_request',
                $leaveRequest->id,
                $oldValues,
                [
                    'status' => $status,
                    'reviewed_by' => $actor->id,
                    'rejection_reason' => $leaveRequest->rejection_reason,
                ]
            );
        }, 3);
    }
}
