@extends('admin.layout.masterlayout')
@section('title', 'Nhật ký thao tác')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4"><h1 class="h4 mb-0">Nhật ký thao tác</h1></div>
<form method="GET" class="d-flex gap-2 mb-3"><input class="form-control" name="action" value="{{ request('action') }}" placeholder="Mã thao tác" aria-label="Mã thao tác" maxlength="50"><button class="btn btn-outline-primary">Lọc</button></form>
<div class="card table-responsive"><table class="table"><thead><tr><th>Thời gian</th><th>Người thực hiện</th><th>Thao tác</th><th>Đối tượng</th><th>Thay đổi</th></tr></thead><tbody>
@forelse($logs as $log)<tr><td>{{ $log->created_at->format('d/m/Y H:i:s') }}</td><td>{{ $log->user?->employee?->full_name ?? 'Hệ thống' }}</td><td>{{ $log->action }}</td><td>{{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}</td><td><details><summary>Xem</summary><strong>Trước</strong><pre class="small">{{ json_encode($log->old_values, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) }}</pre><strong>Sau</strong><pre class="small">{{ json_encode($log->new_values, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) }}</pre></details></td></tr>@empty<tr><td colspan="5">Chưa có nhật ký.</td></tr>@endforelse</tbody></table></div><div class="mt-3">{{ $logs->links('pagination::bootstrap-5') }}</div>
@endsection
