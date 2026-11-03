@extends('admin.layout.masterlayout')
@section('title', 'Chi tiết phòng ban')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h4 mb-0">Chi tiết phòng ban</h1>
</div>
<div class="card p-4">
    <h2 class="h5 mb-2">{{ $department->name }}</h2>
    <p class="text-muted" style="white-space:pre-wrap">{{ $department->description ?: 'Chưa có mô tả.' }}</p>
    <p class="mb-2"><strong>Số nhân viên hiện tại:</strong> {{ $department->employees_count }}</p>
    <p class="mb-2"><strong>Trạng thái hoạt động:</strong> @include('admin.partial.status', ['status' => $department->status])</p>
    <p class="mb-3"><strong>Trạng thái phê duyệt:</strong> @include('admin.partial.status', ['status' => $department->approval_status])</p>
    @if($department->rejection_reason)
        <div class="alert alert-danger py-2"><strong>Lý do từ chối:</strong> {{ $department->rejection_reason }}</div>
    @endif

    @if(auth()->user()->isDirector() && $department->approval_status === 'pending_approval')
        <div class="border rounded p-3 mb-3 bg-light">
            <h3 class="h6 fw-bold mb-2">Phê duyệt phòng ban mới</h3>
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <form method="POST" action="{{ route('departments.approve', $department) }}">
                    @csrf
                    <button class="btn btn-success btn-sm">Phê duyệt</button>
                </form>
                <form method="POST" action="{{ route('departments.reject', $department) }}" class="d-flex gap-2">
                    @csrf
                    <input type="text" name="rejection_reason" class="form-control form-control-sm" placeholder="Nhập lý do từ chối..." required>
                    <button class="btn btn-danger btn-sm">Từ chối</button>
                </form>
            </div>
        </div>
    @endif

    <div class="d-flex gap-2">
        @if(auth()->user()->hasManagementAccess())
            <a class="btn btn-primary" href="{{ route('departments.edit', $department) }}">Sửa</a>
        @endif
        <a class="btn btn-light" href="{{ route('departments.index') }}">Quay lại</a>
    </div>
</div>
@endsection
