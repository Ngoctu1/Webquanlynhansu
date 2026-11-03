@extends('admin.layout.masterlayout')
@section('title', 'Nhật ký thao tác hệ thống')

@php
    $defaultEntityTypes = [
        'user' => 'Tài khoản (user)',
        'employee' => 'Nhân viên (employee)',
        'contract' => 'Hợp đồng (contract)',
        'leave_request' => 'Đơn nghỉ phép (leave_request)',
        'department' => 'Phòng ban (department)',
        'position' => 'Chức vụ (position)',
        'employee_document' => 'Tài liệu nhân viên (employee_document)',
        'change_request' => 'Đề xuất thay đổi (change_request)',
    ];
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h4 mb-1">Nhật ký thao tác hệ thống (Audit Logs)</h1>
        <p class="text-muted small mb-0">
            Chỉ dành cho Giám đốc &amp; Admin. Dữ liệu chỉ đọc (Read-only), ghi lại toàn bộ lịch sử thao tác, phê duyệt, tải file nhạy cảm và thay đổi phòng ban/chức vụ.
        </p>
    </div>
</div>

<div class="card p-3 mb-4">
    <form method="GET" action="{{ route('audit.index') }}" class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label small text-muted mb-1">Người thực hiện</label>
            <select name="user_id" class="form-select">
                <option value="">Tất cả người dùng</option>
                @foreach($users ?? [] as $u)
                    <option value="{{ $u->id }}" @selected((string) ($filters['user_id'] ?? '') === (string) $u->id)>
                        {{ $u->employee?->full_name ? ($u->employee->full_name . ' (' . $u->email . ')') : $u->email }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2">
            <label class="form-label small text-muted mb-1">Loại đối tượng</label>
            <select name="entity_type" class="form-select">
                <option value="">Tất cả đối tượng</option>
                @foreach($defaultEntityTypes as $key => $label)
                    <option value="{{ $key }}" @selected(($filters['entity_type'] ?? '') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-1">
            <label class="form-label small text-muted mb-1">ID đối tượng</label>
            <input type="number" name="entity_id" class="form-control" value="{{ $filters['entity_id'] ?? '' }}" placeholder="ID">
        </div>

        <div class="col-md-2">
            <label class="form-label small text-muted mb-1">Mã hành động</label>
            <input class="form-control" name="action" value="{{ $filters['action'] ?? '' }}" placeholder="VD: update, approve...">
        </div>

        <div class="col-md-2">
            <label class="form-label small text-muted mb-1">Từ ngày</label>
            <input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] ?? '' }}">
        </div>

        <div class="col-md-2">
            <label class="form-label small text-muted mb-1">Đến ngày</label>
            <div class="d-flex gap-1">
                <input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] ?? '' }}">
                <button class="btn btn-outline-primary" type="submit">Lọc</button>
                <a href="{{ route('audit.index') }}" class="btn btn-light">Xóa</a>
            </div>
        </div>
    </form>
</div>

<div class="card table-responsive">
    <table class="table align-middle mb-0">
        <thead>
            <tr>
                <th>Thời điểm</th>
                <th>Người thực hiện</th>
                <th>Hành động</th>
                <th>Đối tượng</th>
                <th>Địa chỉ IP</th>
                <th>Giá trị cũ / Giá trị mới</th>
                <th class="text-end">Chi tiết</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
                <tr>
                    <td class="text-nowrap">{{ $log->created_at?->format('d/m/Y H:i:s') }}</td>
                    <td>
                        <div class="fw-semibold">{{ $log->user?->employee?->full_name ?? $log->user?->email ?? 'Hệ thống' }}</div>
                        @if($log->user?->email)
                            <div class="small text-muted">{{ $log->user->email }}</div>
                        @endif
                    </td>
                    <td><span class="badge bg-secondary">{{ $log->action }}</span></td>
                    <td>
                        <code>{{ $log->entity_type }}{{ $log->entity_id ? ' #' . $log->entity_id : '' }}</code>
                    </td>
                    <td><code>{{ $log->ip_address ?? '—' }}</code></td>
                    <td>
                        @if($log->old_values || $log->new_values)
                            <details>
                                <summary class="small text-primary" style="cursor:pointer;">Xem dữ liệu thay đổi</summary>
                                <div class="mt-2">
                                    @if($log->old_values)
                                        <div class="small fw-semibold text-muted">Giá trị cũ (old_values):</div>
                                        <pre class="small bg-light p-2 rounded border mb-2">{{ json_encode($log->old_values, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) }}</pre>
                                    @endif
                                    @if($log->new_values)
                                        <div class="small fw-semibold text-muted">Giá trị mới (new_values):</div>
                                        <pre class="small bg-light p-2 rounded border mb-0">{{ json_encode($log->new_values, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) }}</pre>
                                    @endif
                                </div>
                            </details>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('audit.show', $log) }}" class="btn btn-sm btn-outline-primary">Xem</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">Không tìm thấy bản ghi nhật ký phù hợp.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-3">
    {{ $logs->links('pagination::bootstrap-5') }}
</div>
@endsection
