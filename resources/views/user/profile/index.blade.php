@extends('user.layout.masterlayout')
@section('title', 'Hồ sơ của tôi')
@section('description', 'Thông tin cá nhân, công việc và tài khoản của bạn tại một nơi.')

@section('content')
@php
    $profileAttributes = $employee ? $employee->getAttributes() : [];
    $profileBirthday = $profileAttributes['date_of_birth'] ?? null;
    $profileGender = $profileAttributes['gender'] ?? null;
    $profileGenderLabel = ['male' => 'Nam', 'female' => 'Nữ', 'other' => 'Khác'][$profileGender] ?? ($profileGender ?: 'Chưa cập nhật');
    $employmentBadge = [
        'pending' => ['Chờ nhận việc', 'warning', 'bi-hourglass-split'],
        'working' => ['Đang làm việc', 'success', 'bi-check-circle-fill'],
        'on_leave' => ['Đang nghỉ phép', 'info', 'bi-cup-hot-fill'],
        'resigned' => ['Đã nghỉ việc', 'neutral', 'bi-door-closed-fill'],
    ][$employee?->employment_status] ?? [$employee?->employment_status ?: 'Chưa cập nhật', 'neutral', 'bi-info-circle'];

    $profileAccountStatus = $user->getAttributes()['status'] ?? null;
    $accountBadge = [
        'pending_reset' => ['Chờ kích hoạt', 'warning', 'bi-clock-history'],
        'active' => ['Đang hoạt động', 'success', 'bi-shield-check'],
        'locked' => ['Đã khóa', 'danger', 'bi-lock-fill'],
    ][$profileAccountStatus] ?? [$profileAccountStatus ?: 'Chưa cập nhật', 'neutral', 'bi-shield'];

    $profileNameParts = preg_split('/\s+/u', trim($employee?->full_name ?? $user->email), -1, PREG_SPLIT_NO_EMPTY);
    $profileInitials = mb_strtoupper(mb_substr($profileNameParts[0] ?? 'NV', 0, 1).(count($profileNameParts) > 1 ? mb_substr(end($profileNameParts), 0, 1) : ''));

    // Tính thâm niên công tác nếu có hire_date
    $seniority = null;
    if ($employee?->hire_date) {
        $diffYears = $employee->hire_date->diffInYears(now());
        $diffMonths = $employee->hire_date->diffInMonths(now()) % 12;
        if ($diffYears > 0) {
            $seniority = $diffYears . ' năm' . ($diffMonths > 0 ? ' ' . $diffMonths . ' tháng' : '');
        } elseif ($diffMonths > 0) {
            $seniority = $diffMonths . ' tháng';
        } else {
            $seniority = 'Mới gia nhập';
        }
    }
@endphp

{{-- ================= HERO PROFILE BANNER ================= --}}
<section class="portal-profile-overview mb-4" aria-labelledby="profileName">
    <div class="d-flex flex-wrap align-items-center gap-4">
        {{-- Avatar / Photo --}}
        @if($employee?->avatar)
            <img class="portal-avatar-lg shadow" src="{{ route('employees.photo', $employee) }}" alt="Ảnh đại diện {{ $employee->full_name }}" style="object-fit:cover">
        @else
            <div class="portal-avatar portal-avatar-lg shadow" aria-hidden="true">{{ $profileInitials }}</div>
        @endif

        {{-- Meta text --}}
        <div class="flex-grow-1" style="min-width: 0;">
            <div class="portal-eyebrow d-flex align-items-center gap-2">
                <span>Hồ sơ nhân viên</span>
                <span class="badge bg-white text-primary rounded-pill px-2 py-1" style="font-size:10px;">{{ $employee?->employee_code ?: 'NV' }}</span>
            </div>
            <h1 class="portal-profile-name" id="profileName">{{ $employee?->full_name ?: ($user->email) }}</h1>
            <div class="portal-profile-meta">
                <span><i class="bi bi-briefcase-fill me-1" aria-hidden="true"></i>{{ $employee?->position?->name ?: 'Chưa phân chức vụ' }}</span>
                <span><i class="bi bi-building-fill me-1" aria-hidden="true"></i>{{ $employee?->department?->name ?: 'Chưa phân phòng ban' }}</span>
                @if($employee?->work_email)
                    <span class="d-none d-md-inline-flex"><i class="bi bi-envelope-fill me-1" aria-hidden="true"></i>{{ $employee->work_email }}</span>
                @endif
            </div>
        </div>

        {{-- Badge trạng thái --}}
        <div class="text-end">
            <span class="portal-badge portal-badge-{{ $employmentBadge[1] }} shadow-sm">
                <i class="bi {{ $employmentBadge[2] }} me-1" aria-hidden="true"></i>{{ $employmentBadge[0] }}
            </span>
        </div>
    </div>

    {{-- Footer strip --}}
    <div class="portal-profile-footer">
        <div>
            <span class="opacity-75 me-2">Mã nhân viên:</span>
            <strong>{{ $employee?->employee_code ?: 'Chưa cấp' }}</strong>
        </div>
        <div>
            <span class="opacity-75 me-2">Ngày vào làm:</span>
            <strong>{{ $employee?->hire_date?->format('d/m/Y') ?? 'Chưa cập nhật' }}</strong>
        </div>
        @if($seniority)
        <div>
            <span class="opacity-75 me-2">Thâm niên:</span>
            <strong>{{ $seniority }}</strong>
        </div>
        @endif
        <div class="ms-md-auto">
            <span class="opacity-75 me-2">Tài khoản:</span>
            <strong>{{ $accountBadge[0] }}</strong>
        </div>
    </div>
</section>

{{-- ================= QUICK STATS ROW ================= --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="portal-stat-card">
            <div class="portal-stat-icon portal-stat-icon-indigo">
                <i class="bi bi-person-vcard"></i>
            </div>
            <div>
                <div class="portal-stat-title">Mã nhân sự</div>
                <div class="portal-stat-value" style="font-size:20px;">{{ $employee?->employee_code ?: '—' }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="portal-stat-card">
            <div class="portal-stat-icon portal-stat-icon-emerald">
                <i class="bi bi-diagram-3"></i>
            </div>
            <div>
                <div class="portal-stat-title">Phòng ban</div>
                <div class="portal-stat-value" style="font-size:18px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:160px;" title="{{ $employee?->department?->name }}">
                    {{ $employee?->department?->name ?: 'Chưa phân' }}
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="portal-stat-card">
            <div class="portal-stat-icon portal-stat-icon-amber">
                <i class="bi bi-calendar-check"></i>
            </div>
            <div>
                <div class="portal-stat-title">Ngày gia nhập</div>
                <div class="portal-stat-value" style="font-size:18px;">{{ $employee?->hire_date?->format('d/m/Y') ?? 'Chưa có' }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="portal-stat-card">
            <div class="portal-stat-icon portal-stat-icon-rose">
                <i class="bi bi-shield-check"></i>
            </div>
            <div>
                <div class="portal-stat-title">Vai trò</div>
                <div class="portal-stat-value" style="font-size:18px;">{{ $user->role?->name ?: 'Nhân viên' }}</div>
            </div>
        </div>
    </div>
</div>

{{-- ================= CHI TIẾT THÔNG TIN ================= --}}
<div class="row g-4">
    {{-- THÔNG TIN CÁ NHÂN --}}
    <div class="col-12 col-xl-7">
        <section class="portal-card h-100" aria-labelledby="personalInformation">
            <div class="portal-card-header">
                <span class="portal-icon"><i class="bi bi-person-fill" aria-hidden="true"></i></span>
                <div>
                    <h2 id="personalInformation">Thông tin cá nhân</h2>
                    <p>Lý lịch cá nhân và thông tin liên hệ của bạn.</p>
                </div>
            </div>
            <div class="portal-card-body">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="portal-info-item">
                            <div class="portal-info-item-icon"><i class="bi bi-person"></i></div>
                            <div class="portal-info-item-content">
                                <dt>Họ và tên</dt>
                                <dd>{{ $employee?->full_name ?: 'Chưa cập nhật' }}</dd>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="portal-info-item">
                            <div class="portal-info-item-icon"><i class="bi bi-calendar-event"></i></div>
                            <div class="portal-info-item-content">
                                <dt>Ngày sinh</dt>
                                <dd>{{ $profileBirthday ? \Illuminate\Support\Carbon::parse($profileBirthday)->format('d/m/Y') : 'Chưa cập nhật' }}</dd>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="portal-info-item">
                            <div class="portal-info-item-icon"><i class="bi bi-gender-ambiguous"></i></div>
                            <div class="portal-info-item-content">
                                <dt>Giới tính</dt>
                                <dd>{{ $profileGenderLabel }}</dd>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="portal-info-item">
                            <div class="portal-info-item-icon"><i class="bi bi-telephone"></i></div>
                            <div class="portal-info-item-content">
                                <dt>Số điện thoại</dt>
                                <dd>
                                    @if($employee?->phone)
                                        <a href="tel:{{ $employee->phone }}" class="text-decoration-none text-primary fw-bold">{{ $employee->phone }}</a>
                                    @else
                                        Chưa cập nhật
                                    @endif
                                </dd>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="portal-info-item">
                            <div class="portal-info-item-icon"><i class="bi bi-card-heading"></i></div>
                            <div class="portal-info-item-content">
                                <dt>Số CCCD (12 số)</dt>
                                <dd>{{ $employee?->citizen_id ?: 'Chưa cập nhật' }}</dd>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="portal-info-item">
                            <div class="portal-info-item-icon"><i class="bi bi-envelope"></i></div>
                            <div class="portal-info-item-content">
                                <dt>Email cá nhân</dt>
                                <dd>{{ $employee?->personal_email ?: 'Chưa cập nhật' }}</dd>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="portal-info-item">
                            <div class="portal-info-item-icon"><i class="bi bi-geo-alt"></i></div>
                            <div class="portal-info-item-content">
                                <dt>Địa chỉ thường trú</dt>
                                <dd>{{ $employee?->address ?: 'Chưa cập nhật' }}</dd>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    {{-- THÔNG TIN CÔNG VIỆC --}}
    <div class="col-12 col-xl-5">
        <section class="portal-card h-100" aria-labelledby="workInformation">
            <div class="portal-card-header">
                <span class="portal-icon portal-icon-teal"><i class="bi bi-briefcase-fill" aria-hidden="true"></i></span>
                <div>
                    <h2 id="workInformation">Thông tin công việc</h2>
                    <p>Vị trí, phòng ban và nhiệm vụ chuyên môn.</p>
                </div>
            </div>
            <div class="portal-card-body">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="portal-info-item">
                            <div class="portal-info-item-icon"><i class="bi bi-building"></i></div>
                            <div class="portal-info-item-content">
                                <dt>Phòng ban trực thuộc</dt>
                                <dd class="text-primary">{{ $employee?->department?->name ?: 'Chưa phân bổ' }}</dd>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="portal-info-item">
                            <div class="portal-info-item-icon"><i class="bi bi-award"></i></div>
                            <div class="portal-info-item-content">
                                <dt>Chức vụ đảm nhiệm</dt>
                                <dd>{{ $employee?->position?->name ?: 'Chưa bổ nhiệm' }}</dd>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="portal-info-item">
                            <div class="portal-info-item-icon"><i class="bi bi-envelope-at"></i></div>
                            <div class="portal-info-item-content">
                                <dt>Email công việc</dt>
                                <dd>{{ $employee?->work_email ?: 'Chưa cấp' }}</dd>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="portal-info-item">
                            <div class="portal-info-item-icon"><i class="bi bi-calendar2-plus"></i></div>
                            <div class="portal-info-item-content">
                                <dt>Ngày vào làm</dt>
                                <dd>{{ $employee?->hire_date?->format('d/m/Y') ?? 'Chưa cập nhật' }}</dd>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="portal-info-item">
                            <div class="portal-info-item-icon"><i class="bi bi-activity"></i></div>
                            <div class="portal-info-item-content">
                                <dt>Tình trạng</dt>
                                <dd><span class="portal-badge portal-badge-{{ $employmentBadge[1] }}">{{ $employmentBadge[0] }}</span></dd>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="portal-info-item">
                            <div class="portal-info-item-icon"><i class="bi bi-calendar-heart"></i></div>
                            <div class="portal-info-item-content">
                                <dt>Tiêu chuẩn phép năm</dt>
                                <dd>{{ $employee?->annual_leave_days ?? 12 }} ngày / năm</dd>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    {{-- TÀI LIỆU CÁ NHÂN ĐÍNH KÈM --}}
    <div class="col-12">
        <section class="portal-card" aria-labelledby="myDocuments">
            <div class="portal-card-header">
                <span class="portal-icon"><i class="bi bi-folder2-open" aria-hidden="true"></i></span>
                <div>
                    <h2 id="myDocuments">Tài liệu cá nhân của tôi</h2>
                    <p>CCCD, Sơ yếu lý lịch (CV), Bằng cấp và các tài liệu lưu trữ trong hồ sơ nhân sự.</p>
                </div>
            </div>
            <div class="portal-card-body">
                @php($myDocs = $documents ?? $employee?->employeeDocuments ?? collect())
                @if($myDocs->count() > 0)
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Loại tài liệu</th>
                                    <th>Tiêu đề</th>
                                    <th>Ngày lưu</th>
                                    <th>Tải xuống</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($myDocs as $doc)
                                <tr>
                                    <td><span class="badge bg-secondary">{{ strtoupper($doc->doc_type) }}</span></td>
                                    <td class="fw-semibold">{{ $doc->title }}</td>
                                    <td>{{ $doc->created_at?->format('d/m/Y H:i') ?? '—' }}</td>
                                    <td>
                                        <a href="{{ route('documents.download', $doc) }}" class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-download me-1"></i>Tải xuống
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted mb-0">Chưa có tài liệu đính kèm nào trong hồ sơ của bạn.</p>
                @endif
            </div>
        </section>
    </div>

    {{-- THÔNG TIN TÀI KHOẢN & BẢO MẬT --}}
    <div class="col-12">
        <section class="portal-card" aria-labelledby="accountInformation">
            <div class="portal-card-header">
                <span class="portal-icon portal-icon-amber"><i class="bi bi-shield-lock-fill" aria-hidden="true"></i></span>
                <div>
                    <h2 id="accountInformation">Thông tin tài khoản &amp; Bảo mật</h2>
                    <p>Thông tin đăng nhập và quyền truy cập vào hệ thống nội bộ.</p>
                </div>
            </div>
            <div class="portal-card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="portal-info-item">
                            <div class="portal-info-item-icon"><i class="bi bi-person-badge"></i></div>
                            <div class="portal-info-item-content">
                                <dt>Email đăng nhập</dt>
                                <dd class="text-break">{{ $user->email }}</dd>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="portal-info-item">
                            <div class="portal-info-item-icon"><i class="bi bi-person-gear"></i></div>
                            <div class="portal-info-item-content">
                                <dt>Vai trò người dùng</dt>
                                <dd><span class="badge bg-primary rounded-pill px-3 py-1">{{ $user->role?->name ?: 'Nhân viên' }}</span></dd>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="portal-info-item">
                            <div class="portal-info-item-icon"><i class="bi bi-check2-circle"></i></div>
                            <div class="portal-info-item-content">
                                <dt>Trạng thái tài khoản</dt>
                                <dd><span class="portal-badge portal-badge-{{ $accountBadge[1] }}">{{ $accountBadge[0] }}</span></dd>
                            </div>
                        </div>
                    </div>
                </div>
                <hr class="my-4">
                <h3 class="h6 fw-bold mb-3"><i class="bi bi-key me-1"></i>Đổi mật khẩu cá nhân</h3>
                <form method="POST" action="{{ route('user.password.update') }}" class="row g-3" style="max-width: 720px;">
                    @csrf
                    @method('PUT')
                    <div class="col-md-4">
                        <label for="current_password" class="form-label small fw-semibold">Mật khẩu hiện tại</label>
                        <input type="password" name="current_password" id="current_password" class="form-control form-control-sm" required autocomplete="current-password">
                    </div>
                    <div class="col-md-4">
                        <label for="password" class="form-label small fw-semibold">Mật khẩu mới</label>
                        <input type="password" name="password" id="password" class="form-control form-control-sm" required minlength="8" maxlength="72" autocomplete="new-password">
                    </div>
                    <div class="col-md-4">
                        <label for="password_confirmation" class="form-label small fw-semibold">Xác nhận mật khẩu mới</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control form-control-sm" required minlength="8" maxlength="72" autocomplete="new-password">
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-sm">Cập nhật mật khẩu</button>
                    </div>
                </form>

                <div class="portal-notice mt-4">
                    <i class="bi bi-shield-check"></i>
                    <div>
                        <strong>Bảo mật thông tin:</strong> Nếu có bất kỳ thông tin nào chưa chính xác (số điện thoại, địa chỉ, tài khoản), vui lòng liên hệ phòng <strong>Nhân sự (HR)</strong> để được hỗ trợ cập nhật hồ sơ.
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection
