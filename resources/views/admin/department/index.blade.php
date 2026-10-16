@extends('admin.layout.masterlayout')
@section('title', 'Danh sách phòng ban')
@section('styles')
<style>
    .catalog-card { background:#fff; border:1px solid var(--border-color); border-radius:var(--radius); box-shadow:var(--shadow-sm); }
    .catalog-table th { background:#f8fafc; color:#64748b; font-size:.8rem; white-space:nowrap; }
    .catalog-table td, .catalog-table th { padding:14px; vertical-align:middle; }
    .catalog-description { white-space:pre-wrap; overflow-wrap:anywhere; }
</style>
@endsection
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4"><h1 class="h4 mb-0">Danh sách phòng ban</h1></div>
@if(auth()->user()->canManageHrm())<a href="{{ route('departments.create') }}" class="btn btn-primary mb-3">Thêm phòng ban</a>@endif
<form method="GET" class="d-flex gap-2 mb-3"><input class="form-control" name="q" value="{{ request('q') }}" placeholder="Tìm mã, tên, mô tả" aria-label="Tìm kiếm"><select name="status" class="form-select" aria-label="Trạng thái"><option value="">Tất cả trạng thái</option><option value="active" @selected(request('status')==='active')>Hoạt động</option><option value="inactive" @selected(request('status')==='inactive')>Ngừng sử dụng</option></select><button class="btn btn-outline-primary">Lọc</button></form>
<div class="card table-responsive"><table class="table align-middle"><thead><tr><th>Mã</th><th>Tên</th><th>Mô tả</th><th>Nhân viên</th><th>Trạng thái</th><th>Thao tác</th></tr></thead><tbody>
@forelse($departments as $item)<tr><td>{{ $item->code }}</td><td>{{ $item->name }}</td><td>{{ \Illuminate\Support\Str::limit($item->description, 120) }}</td><td>{{ $item->employees_count }}</td><td>@include('admin.partial.status', ['status' => $item->status])</td><td><a href="{{ route('departments.show', $item) }}" class="btn btn-sm btn-outline-primary">Xem</a>
@if(auth()->user()->canManageHrm())<a href="{{ route('departments.edit', $item) }}" class="btn btn-sm btn-outline-secondary">Sửa</a>
<form class="d-inline" method="POST" action="{{ route('departments.status', $item) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="{{ $item->status === 'active' ? 'inactive' : 'active' }}"><button class="btn btn-sm btn-outline-secondary">{{ $item->status === 'active' ? 'Ngừng sử dụng' : 'Kích hoạt' }}</button></form>@endif</td></tr>
@empty<tr><td colspan="6">Chưa có dữ liệu.</td></tr>@endforelse</tbody></table></div><div class="mt-3">{{ $departments->links('pagination::bootstrap-5') }}</div>
@endsection
