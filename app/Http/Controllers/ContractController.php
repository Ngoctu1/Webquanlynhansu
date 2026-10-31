<?php
namespace App\Http\Controllers;

use App\Http\Requests\{FilterContractsRequest, SaveContractRequest, RejectApprovalRequest, UploadSignedContractRequest};

use App\Models\{Contract, Employee};
use App\Services\ContractService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ContractController extends Controller
{
    public function index(FilterContractsRequest $request)
    {
        $filters = $request->validated();
        $contracts = Contract::with('employee')
            ->when(isset($filters['q']), fn ($q) => $q->where(fn ($q) => $q->where('contract_code', 'like', '%'.$filters['q'].'%')->orWhereHas('employee', fn ($q) => $q->where('full_name', 'like', '%'.$filters['q'].'%'))))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->latest('id')->paginate(15)->withQueryString();
        return view('admin.contract.index', compact('contracts'));
    }

    public function create() { return view('admin.contract.create', $this->options()); }
    public function store(SaveContractRequest $request, ContractService $service)
    {
        $contract = $service->save($request->user(), $request->payload());
        return redirect()->route('contracts.show', $contract)->with('success', 'Đã tạo bản nháp hợp đồng.');
    }
    public function show(Contract $contract)
    {
        $contract->load(['employee', 'previousContract', 'creator.employee', 'submitter.employee', 'approver.employee']);
        return view('admin.contract.show', compact('contract'));
    }
    public function edit(Contract $contract)
    {
        abort_unless(in_array($contract->status, ['draft', 'rejected'], true), 403);
        return view('admin.contract.edit', ['contract' => $contract, ...$this->options()]);
    }
    public function update(SaveContractRequest $request, Contract $contract, ContractService $service)
    {
        $service->save($request->user(), $request->payload(), $contract);
        return redirect()->route('contracts.show', $contract)->with('success', 'Đã lưu hợp đồng.');
    }
    public function submit(Request $request, Contract $contract, ContractService $service)
    {
        $service->transition($request->user(), $contract, 'submit');
        return back()->with('success', 'Đã trình Director hoặc Admin duyệt.');
    }
    public function approve(Request $request, Contract $contract, ContractService $service)
    {
        $service->transition($request->user(), $contract, 'approve');
        return back()->with('success', 'Đã duyệt hợp đồng. HR có thể chủ động gửi lời mời từ hồ sơ nhân viên.');
    }
    public function reject(RejectApprovalRequest $request, Contract $contract, ContractService $service)
    {
        $data = $request->validated();
        $service->transition($request->user(), $contract, 'reject', $data['rejection_reason']);
        return back()->with('success', 'Đã từ chối hợp đồng.');
    }
    public function uploadSignedDocument(UploadSignedContractRequest $request, Contract $contract)
    {
        abort_unless($contract->status === 'approved', 403, 'Chỉ tải bản scan sau khi hợp đồng được duyệt nội bộ.');
        $newPath = $request->file('signed_document')->store('contract-documents', 'local');
        $oldPath = null;
        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($contract, $newPath, &$oldPath) {
                $contract = Contract::lockForUpdate()->findOrFail($contract->id);
                $old = $contract->getAttributes();
                $oldPath = $contract->signed_document_path;
                $contract->update(['signed_document_path' => $newPath]);
                \App\Services\AuditService::record('CONTRACT_SIGNED_DOCUMENT_UPLOADED', $contract, $old);
            }, 3);
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($newPath);
            throw $e;
        }
        if ($oldPath && $oldPath !== $newPath && !Contract::where('signed_document_path', $oldPath)->exists()) {
            Storage::disk('local')->delete($oldPath);
        }
        return back()->with('success', 'Đã lưu bản scan hợp đồng đã ký.');
    }
    public function signedDocument(Contract $contract): StreamedResponse
    {
        $user = request()->user();
        abort_unless($user->canManageHrm() || ((int) $user->employee_id === (int) $contract->employee_id && $contract->status === 'approved'), 403);
        abort_unless($contract->signed_document_path && Storage::disk('local')->exists($contract->signed_document_path), 404);
        return Storage::disk('local')->response($contract->signed_document_path);
    }
    private function options(): array
    {
        return ['employees' => Employee::orderBy('employee_code')->get(),
            'previousContracts' => Contract::with('employee')->where('status', 'approved')->orderBy('contract_code')->get()];
    }
}
