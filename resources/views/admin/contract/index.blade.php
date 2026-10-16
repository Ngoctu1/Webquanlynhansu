@extends('admin.layout.masterlayout')
@section('title', 'Quản lý hợp đồng')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4"><h1 class="h4 mb-0">Quản lý hợp đồng</h1></div>
@if(auth()->user()->canManageHrm())<a href="{{ route('contracts.create') }}" class="btn btn-primary mb-3">Tạo hợp đồng</a>@endif
<form method="GET" class="d-flex gap-2 mb-3"><input class="form-control" name="q" value="{{ request('q') }}" placeholder="Mã hợp đồng hoặc tên nhân viên" aria-label="Tìm hợp đồng"><select name="status" class="form-select" aria-label="Trạng thái"><option value="">Tất cả trạng thái</option>@foreach(['draft'=>'Bản nháp','pending_approval'=>'Chờ duyệt','approved'=>'Đã duyệt','rejected'=>'Bị từ chối'] as $key=>$label)<option value="{{ $key }}" @selected(request('status')===$key)>{{ $label }}</option>@endforeach</select><button class="btn btn-outline-primary">Lọc</button></form>
<div class="card table-responsive"><table class="table"><thead><tr><th>Mã hợp đồng</th><th>Nhân viên</th><th>Hiệu lực từ</th><th>Hiệu lực đến</th><th>Trạng thái</th><th>Thao tác</th></tr></thead><tbody>
@forelse($contracts as $contract)<tr><td>{{ $contract->contract_code }}</td><td>{{ $contract->employee->full_name }}</td><td>{{ $contract->effective_from->format('d/m/Y') }}</td><td>{{ $contract->effective_to?->format('d/m/Y') ?? 'Không xác định' }}</td><td>@include('admin.partial.status', ['status'=>$contract->status])</td><td><a class="btn btn-sm btn-outline-primary" href="{{ route('contracts.show', $contract) }}">Xem</a></td></tr>@empty<tr><td colspan="6">Chưa có hợp đồng.</td></tr>@endforelse</tbody></table></div><div class="mt-3">{{ $contracts->links('pagination::bootstrap-5') }}</div>
@endsection
