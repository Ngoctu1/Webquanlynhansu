@extends('admin.layout.masterlayout')
@section('title', 'Hồ sơ nhân viên')
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
<div class="d-flex justify-content-between align-items-center mb-4"><h1 class="h4 mb-0">Hồ sơ nhân viên</h1></div>
<div class="card p-4 mb-3"><p>Lịch làm việc: {{ $employee->workSchedule->name }}</p><h2 class="h5">{{ $employee->full_name }} — {{ $employee->employee_code }}</h2>
<div class="row"><div class="col-md-6 mb-2"><strong>Email cá nhân:</strong> {{ $employee->personal_email ?? '—' }}</div><div class="col-md-6 mb-2"><strong>Email công việc:</strong> {{ $employee->work_email ?? '—' }}</div><div class="col-md-6 mb-2"><strong>Điện thoại:</strong> {{ $employee->phone ?? '—' }}</div><div class="col-md-6 mb-2"><strong>Địa chỉ:</strong> {{ $employee->address ?? '—' }}</div>
<div class="col-md-6 mb-2">Ngày sinh: {{ $employee->date_of_birth?->format('d/m/Y') ?? '—' }}</div><div class="col-md-6 mb-2">Giới tính: {{ ['male'=>'Nam','female'=>'Nữ','other'=>'Khác'][$employee->gender] ?? '—' }}</div>
<div class="col-md-6 mb-2">Ngày nhận việc: {{ $employee->hire_date?->format('d/m/Y') ?? '—' }}</div><div class="col-md-6 mb-2">Phòng ban: {{ $employee->department->name }}</div><div class="col-md-6 mb-2">Chức vụ: {{ $employee->position->name }}</div></div>
<p>@include('admin.partial.status', ['status' => $employee->employment_status])</p>
@if(auth()->user()->canManageHrm())<div><a class="btn btn-outline-primary" href="{{ route('employees.edit', $employee) }}">Sửa hồ sơ</a> <a class="btn btn-outline-primary" href="{{ route('contracts.create') }}">Tạo hợp đồng</a></div>@endif</div>
<div class="card p-4"><h2 class="h5">Tài khoản</h2>
@if($employee->user)<p>{{ $employee->user->email }} — {{ $employee->user->role->name }}</p><p>@include('admin.partial.status', ['status' => $employee->user->status])</p>
@if($employee->user->status === 'pending_reset' && !$employee->user->activation_token_hash)<p class="text-warning">Lời mời chưa gửi được hoặc đã bị vô hiệu hóa. Cần kiểm tra cấu hình mail và xử lý gửi lại có kiểm soát; hệ thống không tự gửi thêm khi lặp thao tác.</p>@endif
@if($employee->user->activation_expires_at)<p>Lời mời hết hạn: {{ $employee->user->activation_expires_at->format('d/m/Y H:i') }}. Nếu chưa nhận thư, kiểm tra hộp thư rác; yêu cầu lặp không gửi thêm thư.</p>@endif
@else<p>Chưa có tài khoản.</p>@endif
@if(auth()->user()->canManageHrm())
@if(!$hasApprovedContract)<p class="text-muted">Cần hợp đồng được Director hoặc Admin phê duyệt trước khi gửi lời mời.</p>
@elseif($employee->employment_status !== 'resigned' && !$employee->user)
<p>Lời mời được gửi đến email cá nhân cho tài khoản mới.</p>
<form method="POST" action="{{ route('employees.account.store', $employee) }}">@csrf<button class="btn btn-primary">Gửi lời mời kích hoạt</button></form>
@endif @endif</div><a class="btn btn-light my-3" href="{{ route('employees.index') }}">Quay lại</a>
@endsection
