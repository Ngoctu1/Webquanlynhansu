{{-- ================================================================
     DANH SÁCH NHÂN VIÊN
     resources/views/admin/employee/index.blade.php
     ================================================================ --}}
@extends('admin.layout.masterlayout')

@section('title', 'Quản lý nhân viên')

@section('styles')
<style>
    /* ---- Bộ lọc ---- */
    .filter-card {
        background: #fff;
        border: 1px solid var(--border-color);
        border-radius: var(--radius);
        padding: 18px 22px;
        margin-bottom: 20px;
        box-shadow: var(--shadow-sm);
    }

    /* ---- Bảng dữ liệu ---- */
    .table-card {
        background: #fff;
        border: 1px solid var(--border-color);
        border-radius: var(--radius);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
    }

    .table-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 22px;
        border-bottom: 1px solid var(--border-color);
        background: #fafbfc;
        flex-wrap: wrap;
        gap: 10px;
    }

    .hrm-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.875rem;
    }

    .hrm-table thead th {
        background: #f8fafc;
        color: #64748b;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .05em;
        padding: 11px 14px;
        border-bottom: 1px solid var(--border-color);
        white-space: nowrap;
    }

    .hrm-table tbody tr:hover td {
        background: #f8faff;
    }

    .hrm-table tbody td {
        padding: 12px 14px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
        color: #1e293b;
    }

    .hrm-table tbody tr:last-child td { border-bottom: none; }

    /* Avatar trong bảng */
    .emp-avatar {
        width: 34px;
        height: 34px;
        border-radius: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.73rem;
        font-weight: 700;
        color: #fff;
        background: linear-gradient(135deg, #2563eb, #7c3aed);
        flex-shrink: 0;
    }

    .emp-name { font-weight: 600; font-size: 0.875rem; color: #1e293b; }
    .emp-id   { font-size: 0.73rem; color: #94a3b8; }

    /* Badge trạng thái */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 0.73rem;
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 20px;
        white-space: nowrap;
    }
    .status-badge i { font-size: 7px; }

    .status-active   { background: #d1fae5; color: #065f46; }
    .status-leave    { background: #fef3c7; color: #92400e; }
    .status-resigned { background: #fee2e2; color: #991b1b; }
    .status-probation{ background: #e0e7ff; color: #3730a3; }

    /* Nút thao tác */
    .action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        border-radius: 8px;
        font-size: 0.85rem;
        border: none;
        transition: background .15s, color .15s;
        text-decoration: none;
    }

    .btn-view   { background: #dbeafe; color: #2563eb; }
    .btn-view:hover   { background: #2563eb; color: #fff; }
    .btn-edit   { background: #d1fae5; color: #059669; }
    .btn-edit:hover   { background: #059669; color: #fff; }
    .btn-delete { background: #fee2e2; color: #dc2626; }
    .btn-delete:hover { background: #dc2626; color: #fff; }

    /* Empty state */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #94a3b8;
    }

    .empty-state i { font-size: 3rem; margin-bottom: 14px; display: block; }
</style>
@endsection

@section('content')

{{-- ===== TIÊU ĐỀ TRANG ===== --}}
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
    <div>
        <h1 class="mb-0" style="font-size:1.5rem;font-weight:700;color:#1e293b;">
            <i class="bi bi-people-fill text-primary me-2"></i> Quản lý nhân viên
        </h1>
        <p class="text-secondary mb-0 mt-1" style="font-size:.875rem;">
            Quản lý toàn bộ thông tin nhân viên trong hệ thống
        </p>
    </div>
    <a href="{{ route('employees.create') }}" class="btn btn-primary d-flex align-items-center gap-2"
       style="border-radius:10px;font-weight:600;font-size:.875rem;padding:9px 18px;">
        <i class="bi bi-person-plus-fill"></i> Thêm nhân viên
    </a>
</div>

{{-- ===== THÔNG BÁO ===== --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2"
         style="border-radius:10px;border:none;background:#d1fae5;color:#065f46;" role="alert">
        <i class="bi bi-check-circle-fill"></i>
        <span>{{ session('success') }}</span>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2"
         style="border-radius:10px;border:none;background:#fee2e2;color:#991b1b;" role="alert">
        <i class="bi bi-exclamation-circle-fill"></i>
        <span>{{ session('error') }}</span>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger" role="alert">
        @foreach($errors->all() as $error)<p class="mb-0">{{ $error }}</p>@endforeach
    </div>
@endif

{{-- ===== BỘ LỌC & TÌM KIẾM ===== --}}
<div class="filter-card">
    <form method="GET" action="{{ route('employees.index') }}" id="filter-form">
        <div class="row g-3 align-items-end">

            {{-- Ô tìm kiếm --}}
            <div class="col-12 col-md-4">
                <label class="form-label fw-600 small text-secondary mb-1">
                    <i class="bi bi-search me-1"></i> Tìm kiếm
                </label>
                <input type="text" name="search" class="form-control" style="border-radius:9px;"
                       placeholder="Mã NV, họ tên, email..."
                       value="{{ request('search') }}">
            </div>

            {{-- Lọc phòng ban --}}
            <div class="col-6 col-md-2">
                <label class="form-label fw-600 small text-secondary mb-1">
                    <i class="bi bi-diagram-3 me-1"></i> Phòng ban
                </label>
                <select name="department_id" class="form-select" style="border-radius:9px;"
                        onchange="document.getElementById('filter-form').submit()">
                    <option value="">-- Tất cả --</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}"
                            {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                            {{  $dept->name ?? 'Phòng ban ' . $dept->id }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Lọc chức vụ --}}
            <div class="col-6 col-md-2">
                <label class="form-label fw-600 small text-secondary mb-1">
                    <i class="bi bi-award me-1"></i> Chức vụ
                </label>
                <select name="position_id" class="form-select" style="border-radius:9px;"
                        onchange="document.getElementById('filter-form').submit()">
                    <option value="">-- Tất cả --</option>
                    @foreach($positions as $pos)
                        <option value="{{ $pos->id }}"
                            {{ request('position_id') == $pos->id ? 'selected' : '' }}>
                            {{  $pos->name ?? 'Chức vụ ' . $pos->id }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Lọc trạng thái --}}
            <div class="col-6 col-md-2">
                <label class="form-label fw-600 small text-secondary mb-1">
                    <i class="bi bi-circle-half me-1"></i> Trạng thái
                </label>
                <select name="status" class="form-select" style="border-radius:9px;"
                        onchange="document.getElementById('filter-form').submit()">
                    <option value="">-- Tất cả --</option>
                    <option value="working" {{ request('status') == 'working' ? 'selected' : '' }}>Đang làm việc</option>
                    <option value="inactive"     {{ request('status') == 'inactive'     ? 'selected' : '' }}>Ngừng hoạt động</option>
                    <option value="resigned"  {{ request('status') == 'resigned'  ? 'selected' : '' }}>Đã nghỉ việc</option>
                </select>
            </div>

            {{-- Nút tìm / reset --}}
            <div class="col-6 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100" style="border-radius:9px;font-weight:600;">
                    <i class="bi bi-search"></i> Tìm
                </button>
                <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary"
                   style="border-radius:9px;" title="Xóa bộ lọc">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>

        </div>
    </form>
</div>

{{-- ===== BẢNG DANH SÁCH ===== --}}
<div class="table-card">
    <div class="table-card-header">
        <div class="fw-600" style="font-size:.9rem;color:#1e293b;">
            <i class="bi bi-table me-2 text-primary"></i>
            Danh sách nhân viên
            <span class="badge ms-2" style="background:#dbeafe;color:#2563eb;font-size:.75rem;border-radius:8px;">
                {{ $employees->total() }} nhân viên
            </span>
        </div>
        <div class="d-flex align-items-center gap-2 text-secondary" style="font-size:.8rem;">
            <i class="bi bi-info-circle"></i>
            Trang {{ $employees->currentPage() }} / {{ $employees->lastPage() }}
        </div>
    </div>

    <div class="table-responsive">
        <table class="hrm-table">
            <thead>
                <tr>
                    <th style="width:50px;">STT</th>
                    <th>Mã NV</th>
                    <th>Họ và tên</th>
                    <th>Email</th>
                    <th>Số điện thoại</th>
                    <th>Phòng ban</th>
                    <th>Chức vụ</th>
                    <th>Trạng thái</th>
                    <th>Tài khoản</th>
                    <th style="width:110px;text-align:center;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $index => $employee)
                <tr>
                    {{-- STT --}}
                    <td class="text-secondary" style="font-size:.82rem;">
                        {{ ($employees->currentPage() - 1) * $employees->perPage() + $index + 1 }}
                    </td>

                    {{-- Mã nhân viên --}}
                    <td>
                        <span class="badge" style="background:#f1f5f9;color:#475569;font-size:.78rem;border-radius:7px;padding:4px 10px;font-weight:600;">
                            {{ $employee->employee_code ?? 'N/A' }}
                        </span>
                    </td>

                    {{-- Họ và tên --}}
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <div class="emp-avatar">
                                {{ strtoupper(mb_substr($employee->full_name ?? 'N', 0, 1)) }}
                            </div>
                            <div>
                                <div class="emp-name">{{ $employee->full_name ?? 'Chưa có tên' }}</div>
                                <div class="emp-id">ID: {{ $employee->id }}</div>
                            </div>
                        </div>
                    </td>

                    {{-- Email --}}
                    <td>
                        <a href="mailto:{{ $employee->email }}" class="text-primary text-decoration-none"
                           style="font-size:.82rem;">
                            {{ $employee->email ?? '—' }}
                        </a>
                    </td>

                    {{-- Số điện thoại --}}
                    <td style="font-size:.85rem;">{{ $employee->phone ?? '—' }}</td>

                    {{-- Phòng ban --}}
                    <td style="font-size:.85rem;">
                        {{ $employee->department?->name ?? '—' }}
                    </td>

                    {{-- Chức vụ --}}
                    <td style="font-size:.85rem;">
                        {{ $employee->position?->name ?? '—' }}
                    </td>

                    {{-- Trạng thái --}}
                    <td>
                        @php
                            $status = $employee->status ?? '';
                            $badgeClass = match($status) {
                                'working' => 'status-active',
                                'inactive'     => 'status-leave',
                                'resigned'  => 'status-resigned',
                                default         => 'status-probation',
                            };
                            $iconClass = match($status) {
                                'working' => 'bi-circle-fill text-success',
                                'inactive'     => 'bi-circle-fill text-warning',
                                'resigned'  => 'bi-circle-fill text-danger',
                                default         => 'bi-circle-fill text-primary',
                            };
                        @endphp
                        <span class="status-badge {{ $badgeClass }}">
                            <i class="bi {{ $iconClass }}"></i>
                            {{ ['working' => 'Đang làm việc', 'resigned' => 'Đã nghỉ việc', 'inactive' => 'Ngừng hoạt động'][$employee->status] ?? '—' }}
                        </span>
                    </td>

                    <td>
                        @if($employee->user)
                            <strong>{{ $employee->user->username }}</strong><br>
                            <span>{{ $employee->user->role?->name ?? '—' }}</span><br>
                            <span>{{ ['pending' => 'Chờ kích hoạt', 'active' => 'Hoạt động', 'locked' => 'Đã khóa', 'disabled' => 'Vô hiệu hóa'][$employee->user->status] ?? $employee->user->status }}</span>
                        @else
                            <span class="text-secondary">Chưa có tài khoản</span><br>
                            <a href="{{ route('employees.show', $employee) }}#create-account" class="btn btn-sm btn-outline-primary mt-1">Tạo tài khoản</a>
                        @endif
                    </td>

                    {{-- Thao tác --}}
                    <td>
                        <div class="d-flex align-items-center justify-content-center gap-1">
                            {{-- Xem --}}
                            <a href="{{ route('employees.show', $employee->id) }}"
                               class="action-btn btn-view" title="Xem chi tiết">
                                <i class="bi bi-eye"></i>
                            </a>

                            {{-- Sửa --}}
                            <a href="{{ route('employees.edit', $employee->id) }}"
                               class="action-btn btn-edit" title="Chỉnh sửa">
                                <i class="bi bi-pencil"></i>
                            </a>

                            {{-- Xóa --}}
                            <form action="{{ route('employees.destroy', $employee->id) }}"
                                  method="POST" class="d-inline"
                                  onsubmit="return confirmDelete()">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-btn btn-delete" title="Xóa">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10">
                        <div class="empty-state">
                            <i class="bi bi-people text-secondary"></i>
                            <p class="fw-600 mb-1" style="font-size:1rem;">Không có nhân viên nào</p>
                            <p class="text-secondary small mb-3">
                                {{ request()->hasAny(['search','department_id','position_id','status'])
                                    ? 'Không tìm thấy nhân viên phù hợp với bộ lọc hiện tại.'
                                    : 'Chưa có dữ liệu nhân viên trong hệ thống.' }}
                            </p>
                            <a href="{{ route('employees.create') }}" class="btn btn-primary btn-sm"
                               style="border-radius:8px;">
                                <i class="bi bi-person-plus me-1"></i> Thêm nhân viên đầu tiên
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Phân trang --}}
    @if($employees->hasPages())
    <div class="d-flex align-items-center justify-content-between px-4 py-3 border-top"
         style="background:#fafbfc;">
        <div class="text-secondary small">
            Hiển thị {{ $employees->firstItem() }}–{{ $employees->lastItem() }}
            trong tổng số {{ $employees->total() }} nhân viên
        </div>
        <div>
            {{ $employees->links('pagination::bootstrap-5') }}
        </div>
    </div>
    @endif
</div>

@endsection

@section('scripts')
<script>
    function confirmDelete() {
        return confirm('Xóa nhân viên khỏi danh sách? Lịch sử nhân sự sẽ được giữ lại.');
    }
</script>
@endsection
