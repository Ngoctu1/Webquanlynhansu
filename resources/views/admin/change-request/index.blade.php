@extends('admin.layout.masterlayout')
@section('title', 'Duyệt đề xuất thay đổi')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h4 mb-0">Duyệt đề xuất thay đổi (Director)</h1>
</div>

@if($pendingDepartments->count() > 0 || $pendingPositions->count() > 0 || (isset($pendingEmployees) && $pendingEmployees->count() > 0) || (isset($pendingContracts) && $pendingContracts->count() > 0))
<div class="card p-4 mb-4">
    <h2 class="h6 fw-bold mb-3">Hồ sơ, Hợp đồng &amp; Danh mục mới chờ phê duyệt ({{ $pendingDepartments->count() + $pendingPositions->count() + ($pendingEmployees->count() ?? 0) + ($pendingContracts->count() ?? 0) }})</h2>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Loại</th>
                    <th>Tên / Mã</th>
                    <th>Chi tiết</th>
                    <th>Thao tác duyệt</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pendingContracts ?? [] as $contract)
                <tr>
                    <td><span class="badge bg-warning text-dark">Hợp đồng mới</span></td>
                    <td class="fw-semibold">
                        <a href="{{ route('contracts.show', $contract) }}">{{ $contract->contract_code }}</a>
                    </td>
                    <td>
                        {{ $contract->employee?->full_name }} ({{ $contract->employee?->employee_code }}) &bull;
                        Lương CB: {{ number_format($contract->base_salary, 0, ',', '.') }} ₫ &bull;
                        @if($contract->file_path)
                            <a href="{{ route('contracts.download-pdf', $contract) }}" class="text-success">Đã có PDF</a>
                        @else
                            <span class="text-danger">Chưa có PDF</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex flex-wrap gap-2">
                            <form method="POST" action="{{ route('contracts.approve', $contract) }}">
                                @csrf
                                <button class="btn btn-success btn-sm" @disabled(empty($contract->file_path))>Duyệt</button>
                            </form>
                            <form method="POST" action="{{ route('contracts.reject', $contract) }}" class="d-flex gap-1">
                                @csrf
                                <input type="text" name="rejection_reason" class="form-control form-control-sm" placeholder="Lý do từ chối..." required>
                                <button class="btn btn-danger btn-sm">Từ chối</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
                @foreach($pendingEmployees ?? [] as $emp)
                <tr>
                    <td><span class="badge bg-success">Nhân viên mới</span></td>
                    <td class="fw-semibold">
                        <a href="{{ route('employees.show', $emp) }}">{{ $emp->full_name }} ({{ $emp->employee_code }})</a>
                    </td>
                    <td>{{ $emp->department?->name }} &bull; {{ $emp->position?->name }} &bull; {{ $emp->work_email }}</td>
                    <td>
                        <div class="d-flex flex-wrap gap-2">
                            <form method="POST" action="{{ route('employees.approve', $emp) }}">
                                @csrf
                                <button class="btn btn-success btn-sm">Duyệt</button>
                            </form>
                            <form method="POST" action="{{ route('employees.reject', $emp) }}" class="d-flex gap-1">
                                @csrf
                                <input type="text" name="rejection_reason" class="form-control form-control-sm" placeholder="Lý do từ chối..." required>
                                <button class="btn btn-danger btn-sm">Từ chối</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
                @foreach($pendingDepartments as $dept)
                <tr>
                    <td><span class="badge bg-info text-dark">Phòng ban</span></td>
                    <td class="fw-semibold">{{ $dept->name }}</td>
                    <td>{{ $dept->description ?: '—' }}</td>
                    <td>
                        <div class="d-flex flex-wrap gap-2">
                            <form method="POST" action="{{ route('departments.approve', $dept) }}">
                                @csrf
                                <button class="btn btn-success btn-sm">Duyệt</button>
                            </form>
                            <form method="POST" action="{{ route('departments.reject', $dept) }}" class="d-flex gap-1">
                                @csrf
                                <input type="text" name="rejection_reason" class="form-control form-control-sm" placeholder="Lý do từ chối..." required>
                                <button class="btn btn-danger btn-sm">Từ chối</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
                @foreach($pendingPositions as $pos)
                <tr>
                    <td><span class="badge bg-primary">Chức vụ</span></td>
                    <td class="fw-semibold">{{ $pos->name }}</td>
                    <td>{{ $pos->description ?: '—' }}</td>
                    <td>
                        <div class="d-flex flex-wrap gap-2">
                            <form method="POST" action="{{ route('positions.approve', $pos) }}">
                                @csrf
                                <button class="btn btn-success btn-sm">Duyệt</button>
                            </form>
                            <form method="POST" action="{{ route('positions.reject', $pos) }}" class="d-flex gap-1">
                                @csrf
                                <input type="text" name="rejection_reason" class="form-control form-control-sm" placeholder="Lý do từ chối..." required>
                                <button class="btn btn-danger btn-sm">Từ chối</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<form method="GET" class="d-flex gap-2 mb-3">
    <select name="status" class="form-select" style="max-width:220px">
        <option value="pending" @selected($status === 'pending')>Đang chờ duyệt</option>
        <option value="approved" @selected($status === 'approved')>Đã duyệt</option>
        <option value="rejected" @selected($status === 'rejected')>Đã từ chối</option>
        <option value="" @selected($status === '')>Tất cả</option>
    </select>
    <button class="btn btn-outline-primary">Lọc</button>
</form>

<div class="card table-responsive">
    <table class="table align-middle mb-0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Đối tượng</th>
                <th>Hành động</th>
                <th>Người đề xuất</th>
                <th>Chi tiết thay đổi</th>
                <th>Trạng thái</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            @forelse($changeRequests as $req)
            <tr>
                <td>#{{ $req->id }}</td>
                <td><span class="badge bg-secondary">{{ $req->entity_type }} #{{ $req->entity_id }}</span></td>
                <td>
                    <span class="badge bg-{{ $req->action === 'delete' ? 'danger' : 'warning text-dark' }}">
                        {{ $req->action === 'delete' ? 'Xóa mềm' : 'Cập nhật' }}
                    </span>
                </td>
                <td>{{ $req->requester?->employee?->full_name ?? $req->requester?->email ?? '—' }}</td>
                <td>
                    <details>
                        <summary>Xem dữ liệu</summary>
                        @if($req->old_data)
                            <strong>Dữ liệu cũ:</strong>
                            <pre class="small mb-2">{{ json_encode($req->old_data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) }}</pre>
                        @endif
                        @if($req->new_data)
                            <strong>Dữ liệu đề xuất:</strong>
                            <pre class="small mb-0">{{ json_encode($req->new_data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) }}</pre>
                        @endif
                    </details>
                </td>
                <td>@include('admin.partial.status', ['status' => $req->status])</td>
                <td>
                    @if($req->status === 'pending' && auth()->id() !== $req->requested_by)
                        <div class="d-flex flex-wrap gap-2">
                            <form method="POST" action="{{ route('change-requests.approve', $req) }}">
                                @csrf
                                <button class="btn btn-success btn-sm">Duyệt</button>
                            </form>
                            <form method="POST" action="{{ route('change-requests.reject', $req) }}" class="d-flex gap-1">
                                @csrf
                                <input type="text" name="rejection_reason" class="form-control form-control-sm" placeholder="Lý do từ chối..." required>
                                <button class="btn btn-danger btn-sm">Từ chối</button>
                            </form>
                        </div>
                    @elseif($req->rejection_reason)
                        <small class="text-danger">Lý do: {{ $req->rejection_reason }}</small>
                    @else
                        —
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center text-muted py-4">Không có đề xuất thay đổi nào.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $changeRequests->links('pagination::bootstrap-5') }}</div>
@endsection
