<?php
namespace App\Services;

use App\Models\{Contract, Employee, User};
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ContractService
{
    public function save(User $actor, array $data, ?Contract $contract = null): Contract
    {
        abort_unless($actor->canManageHrm(), 403);
        unset($data['contract_code']);
        $save = function (?string $code = null) use ($actor, $data, $contract) {
            Employee::lockForUpdate()->findOrFail($contract?->employee_id ?? $data['employee_id']);
            if ($contract) {
                $contract = Contract::lockForUpdate()->findOrFail($contract->id);
                $this->requireState($contract, ['draft', 'rejected']);
                if ((int) $data['employee_id'] !== (int) $contract->employee_id) {
                    throw ValidationException::withMessages(['employee_id' => 'Không đổi nhân viên của hợp đồng đã tạo.']);
                }
            }
            if (!empty($data['previous_contract_id'])) {
                $previous = Contract::findOrFail($data['previous_contract_id']);
                if ($previous->employee_id != $data['employee_id'] || $previous->status !== 'approved'
                    || $previous->id === $contract?->id || $previous->effective_from->toDateString() >= $data['effective_from']) {
                    throw ValidationException::withMessages(['previous_contract_id' => 'Chọn hợp đồng đã duyệt của cùng nhân viên và có ngày bắt đầu trước bản sửa đổi.']);
                }
            }
            $old = $contract?->getAttributes() ?? [];
            if ($contract) { $contract->update($data); }
            else { $contract = Contract::create([...$data, 'contract_code' => $code, 'status' => 'draft', 'created_by' => $actor->id]); }
            AuditService::record($old ? 'CONTRACT_UPDATED' : 'CONTRACT_CREATED', $contract, $old);
            return $contract;
        };
        return $contract ? DB::transaction(fn () => $save(), 3) : app(RecordCodeService::class)->contract($save);
    }

    public function transition(User $actor, Contract $contract, string $action, ?string $reason = null): void
    {
        abort_unless($action === 'submit' ? $actor->canManageHrm() : $actor->canApprove(), 403);
        DB::transaction(function () use ($actor, $contract, $action, $reason) {
            // Serializes all approvals for one employee, including an initially empty range.
            Employee::lockForUpdate()->findOrFail($contract->employee_id);
            $contract = Contract::lockForUpdate()->findOrFail($contract->id);
            $this->requireState($contract, $action === 'submit' ? ['draft', 'rejected'] : ['pending_approval']);
            $old = $contract->getAttributes();
            if ($action === 'submit') {
                $contract->fill(['status' => 'pending_approval', 'submitted_by' => $actor->id, 'submitted_at' => now(),
                    'approved_by' => null, 'approved_at' => null, 'rejection_reason' => null]);
            } elseif ($action === 'approve') {
                $overlap = Contract::where('employee_id', $contract->employee_id)->where('status', 'approved')
                    ->where('id', '<>', $contract->id)
                    ->when($contract->effective_to, fn ($q, $end) => $q->whereDate('effective_from', '<=', $end))
                    ->where(fn ($q) => $q->whereNull('effective_to')->orWhereDate('effective_to', '>=', $contract->effective_from))
                    ->exists();
                if ($overlap) {
                    throw ValidationException::withMessages(['contract' => 'Thời gian chồng lấn hợp đồng đã duyệt. Hợp đồng không có ngày kết thúc cần quy trình chấm dứt được thống nhất trước khi duyệt bản sửa đổi.']);
                }
                $contract->fill(['status' => 'approved', 'approved_by' => $actor->id, 'approved_at' => now(), 'rejection_reason' => null]);
            } else {
                if (!trim((string) $reason)) { throw ValidationException::withMessages(['rejection_reason' => 'Cần lý do từ chối.']); }
                $contract->fill(['status' => 'rejected', 'approved_by' => null, 'approved_at' => null, 'rejection_reason' => $reason]);
            }
            $contract->save();
            AuditService::record('CONTRACT_'.strtoupper($action), $contract, $old);
        }, 3);
    }

    private function requireState(Contract $contract, array $states): void
    {
        if (!in_array($contract->status, $states, true)) {
            throw ValidationException::withMessages(['contract' => 'Trạng thái hợp đồng không cho phép thao tác này.']);
        }
    }
}
