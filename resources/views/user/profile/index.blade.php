@extends('user.layout.masterlayout')
@section('title', 'Hồ sơ của tôi')
@section('description', 'Thông tin cá nhân, công việc và tài khoản của bạn tại một nơi.')

@section('content')
@php
    $profileAttributes = $employee->getAttributes();
    $profileBirthday = $profileAttributes['date_of_birth'] ?? null;
    $profileGender = $profileAttributes['gender'] ?? null;
    $profileGenderLabel = ['male' => 'Nam', 'female' => 'Nữ', 'other' => 'Khác'][$profileGender] ?? ($profileGender ?: 'Chưa cập nhật');
    $employmentBadge = [
        'pending' => ['Chờ nhận việc', 'warning'],
        'working' => ['Đang làm việc', 'success'],
        'on_leave' => ['Đang nghỉ phép', 'info'],
        'resigned' => ['Đã nghỉ việc', 'neutral'],
    ][$employee->employment_status] ?? [$employee->employment_status ?: 'Chưa cập nhật', 'neutral'];
    $profileAccountStatus = $user->getAttributes()['status'] ?? null;
    $accountBadge = [
        'pending_reset' => ['Chờ kích hoạt', 'warning'],
        'active' => ['Đang hoạt động', 'success'],
        'locked' => ['Đã khóa', 'danger'],
    ][$profileAccountStatus] ?? [$profileAccountStatus ?: 'Chưa cập nhật', 'neutral'];
    $profileNameParts = preg_split('/\s+/u', trim($employee->full_name ?? ''), -1, PREG_SPLIT_NO_EMPTY);
    $profileInitials = mb_strtoupper(mb_substr($profileNameParts[0] ?? 'NV', 0, 1).(count($profileNameParts) > 1 ? mb_substr(end($profileNameParts), 0, 1) : ''));
@endphp

<section class="portal-card portal-profile-overview mb-4" aria-labelledby="profileName">
    <div class="d-flex flex-wrap align-items-center gap-4">
        <div class="portal-avatar portal-avatar-lg" aria-hidden="true">{{ $profileInitials }}</div>
        <div class="flex-grow-1" style="min-width: 0;">
            <div class="portal-eyebrow">Hồ sơ nhân viên</div>
            <h2 class="portal-profile-name" id="profileName">{{ $employee->full_name ?: 'Chưa cập nhật' }}</h2>
            <div class="portal-profile-meta">
                <span><i class="bi bi-briefcase" aria-hidden="true"></i>{{ $employee->position?->name ?: 'Chưa cập nhật' }}</span>
                <span><i class="bi bi-building" aria-hidden="true"></i>{{ $employee->department?->name ?: 'Chưa cập nhật' }}</span>
            </div>
        </div>
        <span class="portal-badge portal-badge-{{ $employmentBadge[1] }}"><i class="bi bi-person-check" aria-hidden="true"></i>{{ $employmentBadge[0] }}</span>
    </div>
    <div class="portal-profile-footer">
        <span><span class="portal-muted me-2">Mã nhân viên</span><strong>{{ $employee->employee_code ?: 'Chưa cập nhật' }}</strong></span>
        <span><span class="portal-muted me-2">Ngày nhận việc</span><strong>{{ $employee->hire_date?->format('d/m/Y') ?? 'Chưa cập nhật' }}</strong></span>
    </div>
</section>

<div class="row g-4">
    <div class="col-12 col-xl-7">
        <section class="portal-card h-100" aria-labelledby="personalInformation">
            <div class="portal-card-header">
                <span class="portal-icon"><i class="bi bi-person" aria-hidden="true"></i></span>
                <h2 id="personalInformation">Thông tin cá nhân</h2>
            </div>
            <div class="portal-card-body">
                <dl class="portal-data-grid">
                    <div><dt>Họ và tên</dt><dd>{{ $employee->full_name ?: 'Chưa cập nhật' }}</dd></div>
                    <div><dt>Ngày sinh</dt><dd>{{ $profileBirthday ? \Illuminate\Support\Carbon::parse($profileBirthday)->format('d/m/Y') : 'Chưa cập nhật' }}</dd></div>
                    <div><dt>Giới tính</dt><dd>{{ $profileGenderLabel }}</dd></div>
                    <div><dt>Số điện thoại</dt><dd>{{ $employee->phone ?: 'Chưa cập nhật' }}</dd></div>
                    <div class="portal-data-wide"><dt>Email cá nhân</dt><dd>{{ $employee->personal_email ?: 'Chưa cập nhật' }}</dd></div>
                    <div class="portal-data-wide"><dt>Địa chỉ</dt><dd>{{ $employee->address ?: 'Chưa cập nhật' }}</dd></div>
                </dl>
            </div>
        </section>
    </div>
    <div class="col-12 col-xl-5">
        <section class="portal-card h-100" aria-labelledby="workInformation">
            <div class="portal-card-header">
                <span class="portal-icon portal-icon-teal"><i class="bi bi-briefcase" aria-hidden="true"></i></span>
                <h2 id="workInformation">Thông tin công việc</h2>
            </div>
            <div class="portal-card-body">
                <dl class="portal-data-grid">
                    <div><dt>Mã nhân viên</dt><dd>{{ $employee->employee_code ?: 'Chưa cập nhật' }}</dd></div>
                    <div><dt>Ngày vào làm</dt><dd>{{ $employee->hire_date?->format('d/m/Y') ?? 'Chưa cập nhật' }}</dd></div>
                    <div><dt>Phòng ban</dt><dd>{{ $employee->department?->name ?: 'Chưa cập nhật' }}</dd></div>
                    <div><dt>Chức vụ</dt><dd>{{ $employee->position?->name ?: 'Chưa cập nhật' }}</dd></div>
                    <div><dt>Trạng thái nhân sự</dt><dd><span class="portal-badge portal-badge-{{ $employmentBadge[1] }}">{{ $employmentBadge[0] }}</span></dd></div>
                    <div class="portal-data-wide"><dt>Email công việc</dt><dd>{{ $employee->work_email ?: 'Chưa cập nhật' }}</dd></div>
                </dl>
            </div>
        </section>
    </div>
    <div class="col-12">
        <section class="portal-card" aria-labelledby="accountInformation">
            <div class="portal-card-header">
                <span class="portal-icon"><i class="bi bi-shield-check" aria-hidden="true"></i></span>
                <h2 id="accountInformation">Thông tin tài khoản</h2>
            </div>
            <div class="portal-card-body">
                <dl class="portal-data-grid portal-account-grid">
                    <div><dt>Email đăng nhập</dt><dd>{{ $user->email ?: 'Chưa cập nhật' }}</dd></div>
                    <div><dt>Vai trò</dt><dd>{{ $user->role?->name ?: 'Chưa cập nhật' }}</dd></div>
                    <div><dt>Trạng thái tài khoản</dt><dd><span class="portal-badge portal-badge-{{ $accountBadge[1] }}">{{ $accountBadge[0] }}</span></dd></div>
                </dl>
            </div>
        </section>
    </div>
</div>
@endsection
