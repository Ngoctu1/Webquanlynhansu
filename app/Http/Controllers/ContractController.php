<?php

namespace App\Http\Controllers;

use App\Http\Requests\{FilterContractsRequest, RejectApprovalRequest, SaveContractRequest};
use App\Models\{ChangeRequest, Contract, Employee};
use App\Services\{AuditLogger, RecordCodeService};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Storage};
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ContractController extends Controller
{
    public function index(FilterContractsRequest $request)
    {
        $this->authorize('viewAny', Contract::class);

        $user = $request->user();
        $filters = $request->validated();
        $statusFilter = $filters['status'] ?? 'current';

        $contracts = Contract::with(['employee.department', 'employee.position', 'previousContract', 'approver.employee'])
            ->whereHas('employee', fn ($q) => $q->visibleTo($user))
            ->when($filters['q'] ?? null, function ($q, $keyword) {
                $q->where(function ($sub) use ($keyword) {
                    $sub->where('contract_code', 'like', '%' . $keyword . '%')
                        ->orWhereHas('employee', fn ($eq) => $eq->where('full_name', 'like', '%' . $keyword . '%')
                            ->orWhere('employee_code', 'like', '%' . $keyword . '%'));
                });
            })
            ->when($filters['contract_type'] ?? null, fn ($q, $type) => $q->where('contract_type', $type))
            ->when($filters['expiring'] ?? null, function ($q, $expiring) {
                $months = in_array((string) $expiring, ['1', '30'], true) ? 1 : 2;
                $q->where('status', 'active')
                    ->whereNotNull('effective_to')
                    ->whereBetween('effective_to', [now()->toDateString(), now()->addMonths($months)->toDateString()]);
            })
            ->when($statusFilter, function ($q, $status) {
                return match ($status) {
                    'active' => $q->where('status', 'active')->where('approval_status', 'approved'),
                    'pending_approval' => $q->where('approval_status', 'pending_approval'),
                    'rejected' => $q->where('approval_status', 'rejected'),
                    'expired' => $q->where('status', 'expired'),
                    'terminated' => $q->where('status', 'terminated'),
                    'old' => $q->whereIn('status', ['expired', 'terminated']),
                    'all' => $q,
                    default => $q->where(function ($sub) {
                        $sub->where('status', 'active')
                            ->orWhereIn('approval_status', ['pending_approval', 'rejected']);
                    }),
                };
            })
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.contract.index', compact('contracts', 'statusFilter'));
    }

    public function create(Request $request)
    {
        $this->authorize('create', Contract::class);

        $selectedEmployeeId = $request->query('employee_id');
        $options = $this->formOptions($request->user(), $selectedEmployeeId ? (int) $selectedEmployeeId : null);

        return view('admin.contract.create', $options);
    }

    public function store(SaveContractRequest $request, RecordCodeService $codeService)
    {
        $this->authorize('create', Contract::class);

        $actor = $request->user();
        $data = $request->payload();
        $customCode = !empty($data['contract_code']) ? trim($data['contract_code']) : null;
        unset($data['contract_code']);

        $newFilePath = null;
        if ($request->hasFile('pdf_file')) {
            $uuidFilename = (string) Str::uuid() . '.pdf';
            $newFilePath = $request->file('pdf_file')->storeAs('contracts', $uuidFilename, 'local');
            $data['file_path'] = $newFilePath;
        }

        $applyDirectly = $actor->can('canApplyDirectly', Contract::class);
        if ($applyDirectly && empty($data['file_path'])) {
            throw ValidationException::withMessages([
                'pdf_file' => 'Giám đốc tạo hợp đồng trực tiếp bắt buộc phải đính kèm file PDF đã ký giấy.',
            ]);
        }

        try {
            $saveCallback = function (?string $generatedCode = null) use ($actor, $data, $customCode, $applyDirectly): Contract {
                $employee = Employee::lockForUpdate()->findOrFail($data['employee_id']);

                if ($applyDirectly) {
                    // Quy tắc: Chỉ 1 hợp đồng đang hiệu lực tại 1 thời điểm
                    Contract::where('employee_id', $employee->id)
                        ->where('status', 'active')
                        ->update(['status' => 'expired']);

                    if ($employee->employment_status === 'pending') {
                        $employee->update(['employment_status' => 'working']);
                    }
                }

                $contract = Contract::create([
                    ...$data,
                    'contract_code' => $customCode ?: $generatedCode,
                    'status' => 'active',
                    'approval_status' => $applyDirectly ? 'approved' : 'pending_approval',
                    'approved_by' => $applyDirectly ? $actor->id : null,
                    'approved_at' => $applyDirectly ? now() : null,
                    'rejection_reason' => null,
                ]);

                AuditLogger::log('create', 'contract', $contract->id, null, $contract->toArray());

                return $contract;
            };

            $contract = $customCode
                ? DB::transaction(fn () => $saveCallback($customCode), 3)
                : $codeService->contract($saveCallback);
        } catch (\Throwable $e) {
            if ($newFilePath) {
                Storage::disk('local')->delete($newFilePath);
            }
            throw $e;
        }

        $message = $applyDirectly
            ? 'Đã tạo và kích hoạt hợp đồng lao động thành công.'
            : 'Đã tạo hợp đồng lao động và gửi chờ Giám đốc phê duyệt.';

        return redirect()->route('contracts.show', $contract)->with('success', $message);
    }

    public function show(Contract $contract)
    {
        $contract->load([
            'employee.user.role',
            'employee.department',
            'employee.position',
            'previousContract',
            'amendments',
            'approver.employee',
        ]);

        $this->authorize('view', $contract);

        $pendingChangeRequest = ChangeRequest::with('requester.employee')
            ->where('entity_type', 'contract')
            ->where('entity_id', $contract->id)
            ->where('status', 'pending')
            ->latest('id')
            ->first();

        return view('admin.contract.show', compact('contract', 'pendingChangeRequest'));
    }

    public function edit(Request $request, Contract $contract)
    {
        $contract->load('employee.user.role');

        if (in_array($contract->status, ['expired', 'terminated'], true)) {
            $this->authorize('updateOld', $contract);
        } else {
            $this->authorize('update', $contract);
        }

        $options = $this->formOptions($request->user(), $contract->employee_id, $contract);

        return view('admin.contract.edit', [
            'contract' => $contract,
            ...$options,
        ]);
    }

    public function update(SaveContractRequest $request, Contract $contract)
    {
        $contract->load('employee.user.role');

        if (in_array($contract->status, ['expired', 'terminated'], true)) {
            $this->authorize('updateOld', $contract);
        } else {
            $this->authorize('update', $contract);
        }

        $actor = $request->user();
        $data = $request->payload();
        if (empty($data['contract_code'])) {
            unset($data['contract_code']);
        }

        $newFilePath = null;
        if ($request->hasFile('pdf_file')) {
            $uuidFilename = (string) Str::uuid() . '.pdf';
            $newFilePath = $request->file('pdf_file')->storeAs('contracts', $uuidFilename, 'local');
            $data['file_path'] = $newFilePath;
        }

        $oldValues = $contract->only(array_keys($data));

        // Trường hợp 1: HR sửa hợp đồng đang hiệu lực (active & approved) -> Lưu vào change_requests
        if ($actor->can('requiresApproval', $contract)) {
            $changeRequest = ChangeRequest::create([
                'entity_type' => 'contract',
                'entity_id' => $contract->id,
                'action' => 'update',
                'old_data' => $oldValues,
                'new_data' => $data,
                'requested_by' => $actor->id,
                'status' => 'pending',
            ]);

            AuditLogger::log('request_update', 'contract', $contract->id, $oldValues, [
                'change_request_id' => $changeRequest->id,
                'proposed' => $data,
            ]);

            return redirect()
                ->route('contracts.show', $contract)
                ->with('success', 'Yêu cầu chỉnh sửa hợp đồng đang hiệu lực đã được gửi chờ Giám đốc phê duyệt.');
        }

        // Trường hợp 2: HR sửa hợp đồng bị từ chối (rejected) hoặc đang chờ duyệt (pending_approval)
        if ($actor->isHR() && in_array($contract->approval_status, ['rejected', 'pending_approval', 'draft'], true)) {
            $data['approval_status'] = 'pending_approval';
            $data['rejection_reason'] = null;

            $contract->update($data);

            AuditLogger::log('update_resubmit', 'contract', $contract->id, $oldValues, $contract->only(array_keys($data)));

            return redirect()
                ->route('contracts.show', $contract)
                ->with('success', 'Đã cập nhật hợp đồng và gửi lại chờ Giám đốc phê duyệt.');
        }

        // Trường hợp 3: Director sửa trực tiếp (hợp đồng đang hiệu lực hoặc hợp đồng cũ expired/terminated)
        $contract->update($data);

        AuditLogger::log('update', 'contract', $contract->id, $oldValues, $contract->only(array_keys($data)));

        return redirect()
            ->route('contracts.show', $contract)
            ->with('success', 'Đã cập nhật thông tin hợp đồng thành công.');
    }

    public function approve(Request $request, Contract $contract)
    {
        $contract->load('employee.user.role');
        $this->authorize('approve', $contract);

        if (empty($contract->file_path)) {
            return back()->withErrors([
                'file_path' => 'Không thể phê duyệt: Hợp đồng bắt buộc phải có file PDF đã ký giấy đính kèm.',
            ]);
        }

        $actor = $request->user();

        DB::transaction(function () use ($actor, $contract) {
            $employee = Employee::lockForUpdate()->findOrFail($contract->employee_id);
            $contract = Contract::lockForUpdate()->findOrFail($contract->id);

            if (empty($contract->file_path)) {
                throw ValidationException::withMessages([
                    'file_path' => 'Không thể phê duyệt: Hợp đồng bắt buộc phải có file PDF đã ký giấy đính kèm.',
                ]);
            }

            $oldValues = [
                'status' => $contract->status,
                'approval_status' => $contract->approval_status,
            ];

            // Quy tắc: Chỉ 1 hợp đồng đang hiệu lực tại 1 thời điểm
            $previousActiveIds = Contract::where('employee_id', $contract->employee_id)
                ->where('id', '!=', $contract->id)
                ->where('status', 'active')
                ->where('approval_status', 'approved')
                ->lockForUpdate()
                ->pluck('id')
                ->all();

            if (!empty($previousActiveIds)) {
                Contract::whereIn('id', $previousActiveIds)->update(['status' => 'expired']);
            }

            $contract->update([
                'status' => 'active',
                'approval_status' => 'approved',
                'approved_by' => $actor->id,
                'approved_at' => now(),
                'rejection_reason' => null,
            ]);

            if ($employee->employment_status === 'pending') {
                $employee->update(['employment_status' => 'working']);
            }

            AuditLogger::log('approve', 'contract', $contract->id, $oldValues, [
                'status' => 'active',
                'approval_status' => 'approved',
                'expired_previous_contract_ids' => $previousActiveIds,
            ]);
        });

        return back()->with('success', 'Đã phê duyệt hợp đồng lao động. HR có thể gửi mail kích hoạt tài khoản cho nhân viên.');
    }

    public function reject(RejectApprovalRequest $request, Contract $contract)
    {
        $contract->load('employee.user.role');
        $this->authorize('approve', $contract);

        $actor = $request->user();
        $data = $request->validated();

        $oldValues = [
            'status' => $contract->status,
            'approval_status' => $contract->approval_status,
            'rejection_reason' => $contract->rejection_reason,
        ];

        $contract->update([
            'approval_status' => 'rejected',
            'approved_by' => $actor->id,
            'approved_at' => now(),
            'rejection_reason' => $data['rejection_reason'],
        ]);

        AuditLogger::log('reject', 'contract', $contract->id, $oldValues, [
            'approval_status' => 'rejected',
            'rejection_reason' => $data['rejection_reason'],
        ]);

        return back()->with('success', 'Đã từ chối phê duyệt hợp đồng.');
    }

    public function uploadSignedDocument(Request $request, Contract $contract)
    {
        $contract->load('employee.user.role');
        $this->authorize('update', $contract);

        $request->validate([
            'pdf_file' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ], [
            'pdf_file.required' => 'Vui lòng chọn file PDF hợp đồng đã ký giấy.',
            'pdf_file.mimes' => 'File đính kèm bắt buộc phải là định dạng PDF.',
            'pdf_file.max' => 'Dung lượng file PDF tối đa là 10MB.',
        ]);

        $uuidFilename = (string) Str::uuid() . '.pdf';
        $newPath = $request->file('pdf_file')->storeAs('contracts', $uuidFilename, 'local');
        $oldPath = $contract->file_path;

        $contract->update(['file_path' => $newPath]);

        AuditLogger::log('upload_contract_pdf', 'contract', $contract->id, ['file_path' => $oldPath], ['file_path' => $newPath]);

        return back()->with('success', 'Đã tải lên file PDF hợp đồng đã ký giấy.');
    }

    public function downloadPdf(Contract $contract): StreamedResponse
    {
        $contract->load('employee.user.role');
        $this->authorize('download', $contract);

        abort_unless($contract->file_path && Storage::disk('local')->exists($contract->file_path), 404, 'Không tìm thấy file PDF hợp đồng trên hệ thống.');

        AuditLogger::log(
            'download_contract_pdf',
            'contract',
            $contract->id,
            null,
            [
                'contract_code' => $contract->contract_code,
                'employee_id' => $contract->employee_id,
                'file_path' => $contract->file_path,
            ]
        );

        $downloadName = $contract->contract_code . '_' . Str::slug($contract->employee?->full_name ?? 'hop-dong') . '.pdf';

        return Storage::disk('local')->download($contract->file_path, $downloadName, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    public function signedDocument(Contract $contract): StreamedResponse
    {
        return $this->downloadPdf($contract);
    }

    private function formOptions($user, ?int $selectedEmployeeId = null, ?Contract $currentContract = null): array
    {
        $employees = Employee::visibleTo($user)
            ->with('user.role')
            ->where('approval_status', 'approved')
            ->orderBy('employee_code')
            ->get()
            ->reject(fn (Employee $emp) => $emp->isAdminOrDirector() || ($user->employee_id && (int) $emp->id === (int) $user->employee_id))
            ->values();

        if ($currentContract && $currentContract->employee && !$employees->contains('id', $currentContract->employee_id)) {
            $employees->push($currentContract->employee);
        }

        $previousContracts = Contract::with('employee')
            ->whereIn('employee_id', $employees->pluck('id'))
            ->when($currentContract, fn ($q) => $q->where('id', '!=', $currentContract->id))
            ->orderByDesc('effective_from')
            ->orderByDesc('id')
            ->get();

        // Map employee_id -> latest contract id để tự động gợi ý hợp đồng liền trước khi gia hạn
        $latestContractByEmployee = [];
        foreach ($previousContracts as $prev) {
            if (!isset($latestContractByEmployee[$prev->employee_id])) {
                $latestContractByEmployee[$prev->employee_id] = $prev->id;
            }
        }

        $suggestedPreviousContractId = null;
        if (!$currentContract && $selectedEmployeeId && isset($latestContractByEmployee[$selectedEmployeeId])) {
            $suggestedPreviousContractId = $latestContractByEmployee[$selectedEmployeeId];
        }

        return [
            'employees' => $employees,
            'previousContracts' => $previousContracts,
            'latestContractByEmployee' => $latestContractByEmployee,
            'selectedEmployeeId' => $selectedEmployeeId,
            'suggestedPreviousContractId' => $suggestedPreviousContractId,
        ];
    }
}
