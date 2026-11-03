<?php

namespace App\Http\Controllers;

use App\Http\Requests\RejectApprovalRequest;
use App\Models\{ChangeRequest, Contract, Department, Employee, Position};
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ChangeRequestController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', ChangeRequest::class);

        $status = $request->query('status', 'pending');

        $changeRequests = ChangeRequest::with(['requester.employee', 'reviewer.employee'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $pendingDepartments = Department::where('approval_status', 'pending_approval')->latest('id')->get();
        $pendingPositions = Position::where('approval_status', 'pending_approval')->latest('id')->get();
        $pendingEmployees = Employee::with(['department', 'position'])
            ->where('approval_status', 'pending_approval')
            ->latest('id')
            ->get();
        $pendingContracts = Contract::with('employee')
            ->where('approval_status', 'pending_approval')
            ->latest('id')
            ->get();

        return view('admin.change-request.index', compact(
            'changeRequests',
            'pendingDepartments',
            'pendingPositions',
            'pendingEmployees',
            'pendingContracts',
            'status'
        ));
    }

    public function approve(Request $request, ChangeRequest $changeRequest)
    {
        $this->authorize('review', $changeRequest);

        $actor = $request->user();

        DB::transaction(function () use ($actor, $changeRequest) {
            $changeRequest = ChangeRequest::lockForUpdate()->findOrFail($changeRequest->id);

            if ($changeRequest->status !== 'pending') {
                throw ValidationException::withMessages(['change_request' => 'Đề xuất này đã được xử lý trước đó.']);
            }

            if ((int) $changeRequest->requested_by === (int) $actor->id) {
                abort(403, 'Không được tự duyệt đề xuất của chính mình.');
            }

            $modelClass = match ($changeRequest->entity_type) {
                'department' => Department::class,
                'position' => Position::class,
                'employee' => Employee::class,
                'contract' => Contract::class,
                default => null,
            };

            if ($modelClass) {
                $target = $modelClass::lockForUpdate()->find($changeRequest->entity_id);

                if ($target) {
                    if ($changeRequest->action === 'update' && is_array($changeRequest->new_data)) {
                        $oldValues = $target->only(array_keys($changeRequest->new_data));
                        $target->update($changeRequest->new_data);

                        if (
                            $changeRequest->entity_type === 'employee'
                            && !empty($changeRequest->new_data['work_email'])
                        ) {
                            $target->user()?->update(['email' => $changeRequest->new_data['work_email']]);
                        }

                        if (
                            $changeRequest->entity_type === 'employee'
                            && ($changeRequest->new_data['employment_status'] ?? null) === 'resigned'
                        ) {
                            $target->user()?->update(['status' => 'locked', 'remember_token' => null]);
                            $target->contracts()->where('status', 'active')->update(['status' => 'terminated']);
                        }

                        AuditLogger::log(
                            'apply_approved_update',
                            $changeRequest->entity_type,
                            $target->id,
                            $oldValues,
                            $changeRequest->new_data
                        );
                    } elseif ($changeRequest->action === 'delete') {
                        if (
                            in_array($changeRequest->entity_type, ['department', 'position'], true)
                            && $target->employees()->count() > 0
                        ) {
                            throw ValidationException::withMessages([
                                'change_request' => 'Không thể xóa phòng ban/chức vụ đang còn nhân viên!',
                            ]);
                        }

                        $oldValues = $target->toArray();
                        $target->delete();

                        AuditLogger::log(
                            'apply_approved_delete',
                            $changeRequest->entity_type,
                            $target->id,
                            $oldValues,
                            null
                        );
                    }
                }
            }

            $changeRequest->update([
                'status' => 'approved',
                'reviewed_by' => $actor->id,
                'reviewed_at' => now(),
                'rejection_reason' => null,
            ]);

            AuditLogger::log(
                'approve_change_request',
                'change_request',
                $changeRequest->id,
                ['status' => 'pending'],
                ['status' => 'approved', 'entity_type' => $changeRequest->entity_type, 'entity_id' => $changeRequest->entity_id]
            );
        });

        return back()->with('success', 'Đã phê duyệt và áp dụng đề xuất thay đổi thành công.');
    }

    public function reject(RejectApprovalRequest $request, ChangeRequest $changeRequest)
    {
        $this->authorize('review', $changeRequest);

        $actor = $request->user();
        $data = $request->validated();

        DB::transaction(function () use ($actor, $changeRequest, $data) {
            $changeRequest = ChangeRequest::lockForUpdate()->findOrFail($changeRequest->id);

            if ($changeRequest->status !== 'pending') {
                throw ValidationException::withMessages(['change_request' => 'Đề xuất này đã được xử lý trước đó.']);
            }

            if ((int) $changeRequest->requested_by === (int) $actor->id) {
                abort(403, 'Không được tự xử lý đề xuất của chính mình.');
            }

            $changeRequest->update([
                'status' => 'rejected',
                'reviewed_by' => $actor->id,
                'reviewed_at' => now(),
                'rejection_reason' => $data['rejection_reason'],
            ]);

            AuditLogger::log(
                'reject_change_request',
                'change_request',
                $changeRequest->id,
                ['status' => 'pending'],
                ['status' => 'rejected', 'rejection_reason' => $data['rejection_reason']]
            );
        });

        return back()->with('success', 'Đã từ chối đề xuất thay đổi.');
    }
}
