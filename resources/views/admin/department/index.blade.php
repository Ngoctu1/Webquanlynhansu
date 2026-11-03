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
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h4 mb-0">Danh sách phòng ban</h1>
    @if(auth()->user()->hasManagementAccess())
        <a href="{{ route('departments.create') }}" class="btn btn-primary">Thêm phòng ban</a>
    @endif
</div>

<form method="GET" class="d-flex flex-wrap gap-2 mb-3">
    <input class="form-control" style="max-width:300px" name="q" value="{{ request('q') }}" placeholder="Tìm tên, mô tả phòng ban" aria-label="Tìm kiếm">
    <select name="status" class="form-select" style="max-width:200px" aria-label="Trạng thái">
        <option value="">Tất cả trạng thái</option>
        <option value="active" @selected(request('status')==='active')>Hoạt động</option>
        <option value="inactive" @selected(request('status')==='inactive')>Ngừng sử dụng</option>
    </select>
    <select name="approval_status" class="form-select" style="max-width:200px" aria-label="Trạng thái duyệt">
        <option value="">Tất cả trạng thái duyệt</option>
        <option value="approved" @selected(request('approval_status')==='approved')>Đã duyệt</option>
        <option value="pending_approval" @selected(request('approval_status')==='pending_approval')>Chờ duyệt</option>
        <option value="rejected" @selected(request('approval_status')==='rejected')>Bị từ chối</option>
    </select>
    <button class="btn btn-outline-primary">Lọc</button>
</form>

<div class="card table-responsive">
    <table class="table align-middle mb-0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên phòng ban</th>
                <th>Mô tả</th>
                <th>Nhân viên</th>
                <th>Trạng thái</th>
                <th>Phê duyệt</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
        @forelse($departments as $item)
            <tr>
                <td>#{{ $item->id }}</td>
                <td class="fw-semibold">{{ $item->name }}</td>
                <td>{{ \Illuminate\Support\Str::limit($item->description, 100) ?: '—' }}</td>
                <td><span class="badge bg-secondary">{{ $item->employees_count }}</span></td>
                <td>@include('admin.partial.status', ['status' => $item->status])</td>
                <td>@include('admin.partial.status', ['status' => $item->approval_status])</td>
                <td>
                    <div class="d-flex flex-wrap gap-1">
                        <a href="{{ route('departments.show', $item) }}" class="btn btn-sm btn-outline-primary">Xem</a>
                        @if(auth()->user()->hasManagementAccess())
                            <a href="{{ route('departments.edit', $item) }}" class="btn btn-sm btn-outline-secondary">Sửa</a>
                            <form class="d-inline" method="POST" action="{{ route('departments.status', $item) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="{{ $item->status === 'active' ? 'inactive' : 'active' }}">
                                <button class="btn btn-sm btn-outline-secondary">
                                    {{ $item->status === 'active' ? 'Ngừng sử dụng' : 'Kích hoạt' }}
                                </button>
                            </form>
                            <form class="d-inline" method="POST" action="{{ route('departments.destroy', $item) }}" onsubmit="return confirm('Xác nhận xóa mềm phòng ban này?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Xóa</button>
                            </form>
                        @endif
                        @if(auth()->user()->isDirector() && $item->approval_status === 'pending_approval')
                            <form class="d-inline" method="POST" action="{{ route('departments.approve', $item) }}">
                                @csrf
                                <button class="btn btn-sm btn-success">Duyệt</button>
                            </form>
                        @endif
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center text-muted py-4">Chưa có dữ liệu phòng ban.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $departments->links('pagination::bootstrap-5') }}</div>
@endsection
