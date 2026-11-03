@extends('admin.layout.masterlayout')
@section('title', 'Chi tiết chức vụ')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h4 mb-0">Chi tiết chức vụ</h1>
</div>
<div class="card p-4">
    <h2 class="h5 mb-2">{{ $position->name }}</h2>
    <p class="text-muted" style="white-space:pre-wrap">{{ $position->description ?: 'Chưa có mô tả.' }}</p>
    <p class="mb-2"><strong>Số nhân viên hiện tại:</strong> {{ $position->employees_count }}</p>
    <p class="mb-2"><strong>Trạng thái hoạt động:</strong> @include('admin.partial.status', ['status' => $position->status])</p>
    <p class="mb-3"><strong>Trạng thái phê duyệt:</strong> @include('admin.partial.status', ['status' => $position->approval_status])</p>
    @if($position->rejection_reason)
        <div class="alert alert-danger py-2"><strong>Lý do từ chối:</strong> {{ $position->rejection_reason }}</div>
    @endif

    @if(auth()->user()->isDirector() && $position->approval_status === 'pending_approval')
        <div class="border rounded p-3 mb-3 bg-light">
            <h3 class="h6 fw-bold mb-2">Phê duyệt chức vụ mới</h3>
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <form method="POST" action="{{ route('positions.approve', $position) }}">
                    @csrf
                    <button class="btn btn-success btn-sm">Phê duyệt</button>
                </form>
                <form method="POST" action="{{ route('positions.reject', $position) }}" class="d-flex gap-2">
                    @csrf
                    <input type="text" name="rejection_reason" class="form-control form-control-sm" placeholder="Nhập lý do từ chối..." required>
                    <button class="btn btn-danger btn-sm">Từ chối</button>
                </form>
            </div>
        </div>
    @endif

    <div class="d-flex gap-2">
        @if(auth()->user()->hasManagementAccess())
            <a class="btn btn-primary" href="{{ route('positions.edit', $position) }}">Sửa</a>
        @endif
        <a class="btn btn-light" href="{{ route('positions.index') }}">Quay lại</a>
    </div>
</div>
@endsection
