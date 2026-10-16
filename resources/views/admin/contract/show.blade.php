@extends('admin.layout.masterlayout')
@section('title', 'Chi tiết hợp đồng')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4"><h1 class="h4 mb-0">Chi tiết hợp đồng</h1></div>
<div class="card p-4 mb-3"><h2 class="h5">{{ $contract->contract_code }} — {{ $contract->employee->full_name }}</h2>
<p>Loại: {{ ['probation'=>'Thử việc','fixed_term'=>'Có thời hạn','indefinite'=>'Không xác định thời hạn'][$contract->contract_type] }}</p>
<p>Ngày ký: {{ $contract->signed_date?->format('d/m/Y') ?? '—' }}; hiệu lực: {{ $contract->effective_from->format('d/m/Y') }} → {{ $contract->effective_to?->format('d/m/Y') ?? 'Không xác định' }}</p>
<p>Lương cơ bản: {{ number_format($contract->base_salary, 2) }}; hệ số: {{ $contract->salary_coefficient }}; phụ cấp: {{ number_format($contract->allowance, 2) }}</p>
<p>Hợp đồng trước: {{ $contract->previousContract?->contract_code ?? '—' }}</p>
<p>@include('admin.partial.status', ['status'=>$contract->status])</p>
<p>Người tạo: {{ $contract->creator->employee->full_name }}; trình duyệt: {{ $contract->submitted_at?->format('d/m/Y H:i') ?? '—' }}; người duyệt: {{ $contract->approver?->employee?->full_name ?? '—' }}</p>
@if($contract->rejection_reason)<div class="alert alert-warning">Lý do từ chối: {{ $contract->rejection_reason }}</div>@endif
@if(auth()->user()->canManageHrm())@if(in_array($contract->status, ['draft','rejected']))<div class="d-flex gap-2"><a href="{{ route('contracts.edit', $contract) }}" class="btn btn-outline-primary">Sửa hợp đồng</a><form method="POST" action="{{ route('contracts.submit', $contract) }}">@csrf<button class="btn btn-primary">Trình duyệt</button></form></div>@elseif($contract->status==='approved')<p>Hợp đồng đã khóa. Muốn sửa, tạo hợp đồng mới và chọn hợp đồng trước.</p><a href="{{ route('contracts.create') }}" class="btn btn-outline-primary">Tạo hợp đồng sửa đổi</a>@endif @endif
@if(auth()->user()->canApprove())@if($contract->status==='pending_approval')<form method="POST" action="{{ route('contracts.approve', $contract) }}" class="mb-3">@csrf<button class="btn btn-success">Phê duyệt hợp đồng</button></form><form method="POST" action="{{ route('contracts.reject', $contract) }}">@csrf<label for="rejection_reason" class="form-label">Lý do từ chối</label><textarea id="rejection_reason" name="rejection_reason" class="form-control mb-2" required maxlength="5000">{{ old('rejection_reason') }}</textarea><button class="btn btn-outline-danger">Từ chối</button></form>@endif @endif
</div><a href="{{ route('contracts.index') }}" class="btn btn-light">Quay lại</a>
@endsection
