@extends('admin.layout.masterlayout')
@section('title', 'Chi tiết nhật ký thao tác #' . $auditLog->id)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h4 mb-1">Chi tiết nhật ký thao tác #{{ $auditLog->id }}</h1>
        <p class="text-muted small mb-0">Bản ghi kiểm toán bất biến (không thể chỉnh sửa hoặc xóa).</p>
    </div>
    <a href="{{ route('audit.index') }}" class="btn btn-light">Quay lại danh sách</a>
</div>

<div class="card p-4 mb-4">
    <div class="row g-3">
        <div class="col-md-3">
            <div class="text-muted small">Thời điểm ghi nhận</div>
            <div class="fw-bold">{{ $auditLog->created_at?->format('d/m/Y H:i:s') }}</div>
        </div>
        <div class="col-md-3">
            <div class="text-muted small">Người thực hiện</div>
            <div class="fw-semibold">{{ $auditLog->user?->employee?->full_name ?? $auditLog->user?->email ?? 'Hệ thống' }}</div>
            @if($auditLog->user?->email)
                <div class="small text-muted">{{ $auditLog->user->email }}</div>
            @endif
        </div>
        <div class="col-md-2">
            <div class="text-muted small">Hành động</div>
            <div><span class="badge bg-secondary">{{ $auditLog->action }}</span></div>
        </div>
        <div class="col-md-2">
            <div class="text-muted small">Đối tượng</div>
            <div><code>{{ $auditLog->entity_type }}{{ $auditLog->entity_id ? ' #' . $auditLog->entity_id : '' }}</code></div>
        </div>
        <div class="col-md-2">
            <div class="text-muted small">Địa chỉ IP</div>
            <div><code>{{ $auditLog->ip_address ?? '—' }}</code></div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card p-4 h-100">
            <h2 class="h6 fw-bold mb-3">Giá trị cũ (old_values)</h2>
            @if($auditLog->old_values)
                <pre class="bg-light p-3 rounded border mb-0">{{ json_encode($auditLog->old_values, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) }}</pre>
            @else
                <p class="text-muted mb-0">Không có dữ liệu cũ.</p>
            @endif
        </div>
    </div>
    <div class="col-md-6">
        <div class="card p-4 h-100">
            <h2 class="h6 fw-bold mb-3">Giá trị mới (new_values)</h2>
            @if($auditLog->new_values)
                <pre class="bg-light p-3 rounded border mb-0">{{ json_encode($auditLog->new_values, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) }}</pre>
            @else
                <p class="text-muted mb-0">Không có dữ liệu mới.</p>
            @endif
        </div>
    </div>
</div>
@endsection
