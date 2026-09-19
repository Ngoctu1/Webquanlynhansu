{{-- ================================================================
     XEM CHI TIẾT NHÂN VIÊN
     resources/views/admin/employee/show.blade.php
     ================================================================ --}}
@extends('admin.layout.masterlayout')

@section('title', 'Chi tiết nhân viên — ' . ($employee->ho_ten ?? 'N/A'))

@section('styles')
<style>
    /* ---- Info Card ---- */
    .info-card {
        background: #fff;
        border: 1px solid var(--border-color);
        border-radius: var(--radius);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
    }

    .info-card-header {
        padding: 18px 24px;
        border-bottom: 1px solid var(--border-color);
        background: #fafbfc;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .info-card-header h5 {
        margin: 0;
        font-size: .95rem;
        font-weight: 700;
        color: #1e293b;
    }

    .info-card-body { padding: 24px; }

    /* ---- Info Row ---- */
    .info-row {
        display: flex;
        padding: 12px 0;
        border-bottom: 1px solid #f1f5f9;
        align-items: flex-start;
        gap: 12px;
    }

    .info-row:last-child { border-bottom: none; }

    .info-label {
        min-width: 160px;
        font-size: .78rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: .04em;
        padding-top: 2px;
        flex-shrink: 0;
    }

    .info-value {
        flex: 1;
        font-size: .875rem;
        color: #1e293b;
        font-weight: 500;
    }

    .info-icon {
        width: 28px;
        height: 28px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .85rem;
        flex-shrink: 0;
        background: #f1f5f9;
        color: #2563eb;
    }

    /* ---- Avatar ---- */
    .profile-avatar {
        width: 80px;
        height: 80px;
        border-radius: 20px;
        background: linear-gradient(135deg, #2563eb, #7c3aed);
        color: #fff;
        font-size: 1.8rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* ---- Status Badge ---- */
    .status-badge-lg {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: .82rem;
        font-weight: 600;
        padding: 5px 16px;
        border-radius: 20px;
    }

    .status-active   { background: #d1fae5; color: #065f46; }
    .status-leave    { background: #fef3c7; color: #92400e; }
    .status-resigned { background: #fee2e2; color: #991b1b; }
    .status-probation{ background: #e0e7ff; color: #3730a3; }

    /* ---- Timeline / Meta ---- */
    .meta-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: .8rem;
        color: #64748b;
        padding: 8px 0;
        border-bottom: 1px solid #f1f5f9;
    }

    .meta-item:last-child { border-bottom: none; }

    .meta-item i { color: #2563eb; font-size: 1rem; width: 18px; text-align: center; }
</style>
@endsection

@section('content')

{{-- ===== TIÊU ĐỀ TRANG ===== --}}
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
    <div>
        <h1 class="mb-0" style="font-size:1.5rem;font-weight:700;color:#1e293b;">
            <i class="bi bi-person-badge text-primary me-2"></i> Chi tiết nhân viên
        </h1>
        <p class="text-secondary mb-0 mt-1" style="font-size:.875rem;">
            Xem toàn bộ thông tin của nhân viên
        </p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('employees.edit', $employee->id) }}"
           class="btn btn-warning d-flex align-items-center gap-2"
           style="border-radius:10px;font-weight:600;font-size:.875rem;color:#fff;">
            <i class="bi bi-pencil-fill"></i> Sửa
        </a>
        <a href="{{ route('employees.index') }}"
           class="btn btn-outline-secondary d-flex align-items-center gap-2"
           style="border-radius:10px;font-weight:600;font-size:.875rem;">
            <i class="bi bi-arrow-left"></i> Quay lại danh sách
        </a>
    </div>
</div>

<div class="row g-4">

    {{-- ---- CỘT TRÁI: Profile Card + Meta ---- --}}
    <div class="col-12 col-xl-4">

        {{-- Profile Summary --}}
        <div class="info-card mb-4">
            <div class="info-card-body text-center" style="padding:32px 24px;">

                {{-- Avatar --}}
                <div class="profile-avatar mx-auto mb-3">
                    {{ strtoupper(mb_substr($employee->full_name ?? 'N', 0, 1)) }}
                </div>

                {{-- Tên --}}
                <h4 class="fw-700 mb-1" style="font-size:1.2rem;color:#1e293b;">
                    {{ $employee->full_name ?? 'Chưa có tên' }}
                </h4>

                {{-- Mã nhân viên --}}
                <p class="mb-2">
                    <span class="badge" style="background:#f1f5f9;color:#475569;font-size:.8rem;border-radius:8px;padding:5px 12px;font-weight:600;">
                        <i class="bi bi-tag me-1"></i> {{ $employee->employee_code ?? 'N/A' }}
                    </span>
                </p>

                {{-- Chức vụ --}}
                @if($position)
                <p class="text-secondary mb-2" style="font-size:.875rem;">
                    <i class="bi bi-award me-1 text-primary"></i>
                    {{ $position->position_id ?? $position->name ?? 'N/A' }}
                </p>
                @endif

                {{-- Phòng ban --}}
                @if($department)
                <p class="text-secondary mb-3" style="font-size:.875rem;">
                    <i class="bi bi-diagram-3 me-1 text-primary"></i>
                    {{ $department->department_id ?? $department->name ?? 'N/A' }}
                </p>
                @endif

                {{-- Trạng thái --}}
                @php
                    $status = $employee->trang_thai ?? '';
                    $badgeClass = match($status) {
                        'Đang làm việc' => 'status-active',
                        'Nghỉ phép'     => 'status-leave',
                        'Đã nghỉ việc'  => 'status-resigned',
                        'Thử việc'      => 'status-probation',
                        default         => 'status-probation',
                    };
                @endphp
                <span class="status-badge-lg {{ $badgeClass }}">
                    <i class="bi bi-circle-fill" style="font-size:8px;"></i>
                    {{ $status ?: 'Chưa xác định' }}
                </span>

            </div>
        </div>

        {{-- Thông tin hệ thống --}}
        <div class="info-card">
            <div class="info-card-header">
                <i class="bi bi-clock-history text-primary"></i>
                <h5>Thông tin hệ thống</h5>
            </div>
            <div class="info-card-body" style="padding:16px 20px;">
                <div class="meta-item">
                    <i class="bi bi-hash"></i>
                    <div>
                        <div style="font-size:.72rem;color:#94a3b8;font-weight:600;">ID hệ thống</div>
                        <div style="font-size:.85rem;font-weight:600;color:#1e293b;">#{{ $employee->id }}</div>
                    </div>
                </div>
                <div class="meta-item">
                    <i class="bi bi-calendar-plus"></i>
                    <div>
                        <div style="font-size:.72rem;color:#94a3b8;font-weight:600;">Ngày tạo hồ sơ</div>
                        <div style="font-size:.85rem;font-weight:600;color:#1e293b;">
                            {{ $employee->created_at ? \Carbon\Carbon::parse($employee->created_at)->format('d/m/Y H:i') : '—' }}
                        </div>
                    </div>
                </div>
                <div class="meta-item">
                    <i class="bi bi-pencil-square"></i>
                    <div>
                        <div style="font-size:.72rem;color:#94a3b8;font-weight:600;">Cập nhật lần cuối</div>
                        <div style="font-size:.85rem;font-weight:600;color:#1e293b;">
                            {{ $employee->updated_at ? \Carbon\Carbon::parse($employee->updated_at)->format('d/m/Y H:i') : '—' }}
                        </div>
                    </div>
                </div>
                @if($employee->hire_date)
                <div class="meta-item">
                    <i class="bi bi-calendar-check"></i>
                    <div>
                        <div style="font-size:.72rem;color:#94a3b8;font-weight:600;">Thời gian công tác</div>
                        @php
                            $join = \Carbon\Carbon::parse($employee->hire_date);
                            $now  = \Carbon\Carbon::now();
                            $diff = $join->diff($now);
                        @endphp
                        <div style="font-size:.85rem;font-weight:600;color:#1e293b;">
                            {{ $diff->y }} năm {{ $diff->m }} tháng
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

    </div>

    {{-- ---- CỘT PHẢI: Chi tiết thông tin ---- --}}
    <div class="col-12 col-xl-8">

        {{-- Thông tin cá nhân --}}
        <div class="info-card mb-4">
            <div class="info-card-header">
                <i class="bi bi-person-lines-fill text-primary"></i>
                <h5>Thông tin cá nhân</h5>
            </div>
            <div class="info-card-body">

                <div class="info-row">
                    <div class="info-icon"><i class="bi bi-person"></i></div>
                    <div class="info-label">Họ và tên</div>
                    <div class="info-value">{{ $employee->full_name ?? '—' }}</div>
                </div>

                <div class="info-row">
                    <div class="info-icon"><i class="bi bi-tag"></i></div>
                    <div class="info-label">Mã nhân viên</div>
                    <div class="info-value">
                        <span class="badge" style="background:#dbeafe;color:#2563eb;border-radius:7px;padding:4px 10px;font-weight:700;">
                            {{ $employee->employee_code ?? '—' }}
                        </span>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-icon"><i class="bi bi-cake"></i></div>
                    <div class="info-label">Ngày sinh</div>
                    <div class="info-value">
                        @if($employee->date_of_birth)
                            {{ \Carbon\Carbon::parse($employee->date_of_birth)->format('d/m/Y') }}
                            <span class="text-secondary ms-2" style="font-size:.8rem;">
                                ({{ \Carbon\Carbon::parse($employee->date_of_birth)->age }} tuổi)
                            </span>
                        @else
                            <span class="text-secondary">—</span>
                        @endif
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-icon"><i class="bi bi-gender-ambiguous"></i></div>
                    <div class="info-label">Giới tính</div>
                    <div class="info-value">{{ $employee->gender ?? '—' }}</div>
                </div>

                <div class="info-row">
                    <div class="info-icon"><i class="bi bi-card-text"></i></div>
                    <div class="info-label">Số CCCD / CMND</div>
                    <div class="info-value" style="font-family:monospace;letter-spacing:.05em;">
                        {{ $employee->cccd ?? '—' }}
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-icon"><i class="bi bi-telephone"></i></div>
                    <div class="info-label">Số điện thoại</div>
                    <div class="info-value">
                        @if($employee->phone)
                            <a href="tel:{{ $employee->phone }}" class="text-primary text-decoration-none fw-600">
                                {{ $employee->phone }}
                            </a>
                        @else
                            <span class="text-secondary">—</span>
                        @endif
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-icon"><i class="bi bi-envelope"></i></div>
                    <div class="info-label">Email</div>
                    <div class="info-value">
                        @if($employee->email)
                            <a href="mailto:{{ $employee->email }}" class="text-primary text-decoration-none fw-600">
                                {{ $employee->email }}
                            </a>
                        @else
                            <span class="text-secondary">—</span>
                        @endif
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-icon"><i class="bi bi-geo-alt"></i></div>
                    <div class="info-label">Địa chỉ</div>
                    <div class="info-value">{{ $employee->address ?? '—' }}</div>
                </div>

            </div>
        </div>

        {{-- Thông tin công việc --}}
        <div class="info-card">
            <div class="info-card-header">
                <i class="bi bi-briefcase-fill text-primary"></i>
                <h5>Thông tin công việc</h5>
            </div>
            <div class="info-card-body">

                <div class="info-row">
                    <div class="info-icon"><i class="bi bi-calendar-check"></i></div>
                    <div class="info-label">Ngày vào làm</div>
                    <div class="info-value">
                        {{ $employee->hire_date
                            ? \Carbon\Carbon::parse($employee->hire_date)->format('d/m/Y')
                            : '—' }}
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-icon"><i class="bi bi-diagram-3"></i></div>
                    <div class="info-label">Phòng ban</div>
                    <div class="info-value">
                        @if($department)
                            <span class="badge" style="background:#ede9fe;color:#7c3aed;border-radius:8px;padding:4px 12px;font-weight:600;">
                                {{  $department->name ?? 'N/A' }}
                            </span>
                        @else
                            <span class="text-secondary">Chưa phân công</span>
                        @endif
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-icon"><i class="bi bi-award"></i></div>
                    <div class="info-label">Chức vụ</div>
                    <div class="info-value">
                        @if($position)
                            <span class="badge" style="background:#d1fae5;color:#065f46;border-radius:8px;padding:4px 12px;font-weight:600;">
                                {{ $position->name ?? 'N/A' }}
                            </span>
                        @else
                            <span class="text-secondary">Chưa phân công</span>
                        @endif
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-icon"><i class="bi bi-circle-fill" style="font-size:.6rem;"></i></div>
                    <div class="info-label">Trạng thái</div>
                    <div class="info-value">
                        <span class="status-badge-lg {{ $badgeClass }}">
                            <i class="bi bi-circle-fill" style="font-size:8px;"></i>
                            {{ $employee->status ?: 'Chưa xác định' }}
                        </span>
                    </div>
                </div>

            </div>
        </div>

    </div>

</div>{{-- /row --}}

{{-- ===== FOOTER ACTIONS ===== --}}
<div class="d-flex justify-content-end gap-3 mt-4">
    <form action="{{ route('employees.destroy', $employee->id) }}" method="POST"
          onsubmit="return confirm('Xóa nhân viên này? Thao tác không thể hoàn tác!')">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-outline-danger d-flex align-items-center gap-2"
                style="border-radius:10px;font-weight:600;">
            <i class="bi bi-trash"></i> Xóa nhân viên
        </button>
    </form>
    <a href="{{ route('employees.edit', $employee->id) }}"
       class="btn btn-warning d-flex align-items-center gap-2"
       style="border-radius:10px;font-weight:600;color:#fff;">
        <i class="bi bi-pencil-fill"></i> Sửa thông tin
    </a>
    <a href="{{ route('employees.index') }}"
       class="btn btn-outline-secondary d-flex align-items-center gap-2"
       style="border-radius:10px;font-weight:600;">
        <i class="bi bi-arrow-left"></i> Quay lại danh sách
    </a>
</div>

@endsection
