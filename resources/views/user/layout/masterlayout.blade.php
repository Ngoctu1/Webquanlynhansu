<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#4f46e5">
    <title>@yield('title', 'Hồ sơ của tôi') — Cổng Nhân Viên</title>
    {{-- Bootstrap 5 --}}
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
    {{-- Portal theme riêng biệt (indigo/violet, khác hẳn admin teal-dark) --}}
    <link rel="stylesheet" href="{{ asset('css/portal.css') }}">
    @yield('styles')
</head>
<body>
@php
    $portalUser = auth()->user();
    $portalEmployee = $portalUser?->employee;
    $portalName = $portalEmployee?->full_name ?: ($portalUser?->email ?? 'Nhân viên');
    $portalRole = $portalUser?->role?->name ?: 'Nhân viên';
    $nameParts = preg_split('/\s+/u', trim($portalName), -1, PREG_SPLIT_NO_EMPTY);
    $portalInitials = mb_strtoupper(mb_substr($nameParts[0] ?? 'NV', 0, 1) . (count($nameParts) > 1 ? mb_substr(end($nameParts), 0, 1) : ''));
@endphp
<a class="skip-link visually-hidden-focusable" href="#portal-main">Đi đến nội dung</a>

{{-- ===== SIDEBAR PORTAL ===== --}}
<aside class="sidebar offcanvas-lg offcanvas-start" tabindex="-1" id="sidebar" aria-label="Điều hướng chính">

    {{-- Brand --}}
    <a class="brand" href="{{ route('user.dashboard') }}">
        <span class="brand-icon"><i class="bi bi-person-circle"></i></span>
        <span>Nhân Viên<span class="brand-caption">EMPLOYEE PORTAL</span></span>
    </a>

    {{-- Workspace --}}
    <div class="workspace-label">
        <span class="workspace-dot"></span>
        Cổng cá nhân
        <span class="ms-auto small">NV</span>
    </div>

    {{-- Navigation --}}
    <div class="nav-label">KHÔNG GIAN CỦA BẠN</div>
    <nav class="sidebar-nav">
        <a class="{{ request()->routeIs('user.dashboard') ? 'active' : '' }}"
           href="{{ route('user.dashboard') }}">
            <i class="bi bi-person-badge"></i>Hồ sơ của tôi</a>

        <a class="{{ request()->routeIs('user.contracts') ? 'active' : '' }}"
           href="{{ route('user.contracts') }}">
            <i class="bi bi-file-earmark-text"></i>Hợp đồng lao động</a>

        <a class="{{ request()->routeIs('user.leave-requests.*') ? 'active' : '' }}"
           href="{{ route('user.leave-requests.index') }}">
            <i class="bi bi-calendar2-check"></i>Đăng ký nghỉ phép</a>
    </nav>

    {{-- Sidebar note --}}
    <div class="sidebar-note">
        <i class="bi bi-shield-check"></i>
        <strong>Dữ liệu được bảo mật</strong>
        <p>Hồ sơ và hợp đồng của bạn<br>chỉ bạn mới có thể xem.</p>
    </div>

    {{-- User info + Logout --}}
    <div class="sidebar-user">
        <span class="avatar avatar-light">{{ $portalInitials }}</span>
        <div style="min-width:0; overflow:hidden;">
            <strong style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis; display:block;">
                {{ $portalName }}
            </strong>
            <small>{{ $portalRole }}</small>
        </div>
        <form method="POST" action="{{ route('logout') }}" class="ms-auto flex-shrink-0">
            @csrf
            <button class="logout-btn" aria-label="Đăng xuất" title="Đăng xuất">
                <i class="bi bi-box-arrow-right"></i>
            </button>
        </form>
    </div>

</aside>

{{-- ===== APP SHELL ===== --}}
<div class="app-shell">

    {{-- Topbar --}}
    <header class="topbar">
        <div class="d-flex align-items-center gap-3">
            {{-- Mobile toggle --}}
            <button type="button"
                    class="btn btn-light d-lg-none p-2"
                    data-bs-toggle="offcanvas"
                    data-bs-target="#sidebar"
                    aria-label="Mở menu">
                <i class="bi bi-list fs-5"></i>
            </button>
            <span class="text-muted d-none d-md-inline">Cổng nhân viên</span>
            <i class="bi bi-chevron-right small text-muted d-none d-md-inline"></i>
            <strong>@yield('title', 'Hồ sơ của tôi')</strong>
        </div>
        <div class="topbar-right">
            @if(!empty($notificationCounts['items']))
            <div class="dropdown">
                <button class="btn btn-light btn-sm position-relative d-inline-flex align-items-center gap-1" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-bell"></i>
                    <span class="d-none d-md-inline">Thông báo</span>
                    @if(($notificationCounts['total_badges'] ?? 0) > 0)
                        <span class="badge rounded-pill bg-danger">{{ $notificationCounts['total_badges'] }}</span>
                    @endif
                </button>
                <div class="dropdown-menu dropdown-menu-end shadow border-0 p-2" style="min-width: 300px;">
                    <div class="px-2 py-1 border-bottom mb-1 fw-bold small text-uppercase text-muted">
                        Cập nhật đơn nghỉ phép
                    </div>
                    @foreach($notificationCounts['items'] as $item)
                        <a href="{{ $item['url'] }}" class="dropdown-item rounded py-2 d-flex justify-content-between align-items-center">
                            <div class="pe-2" style="white-space: normal;">
                                <div class="fw-semibold small {{ !empty($item['urgent']) ? 'text-danger' : '' }}">{{ $item['label'] }}</div>
                                <div class="text-muted" style="font-size: 11.5px;">{{ $item['subtext'] }}</div>
                            </div>
                            <span class="badge {{ ($item['count'] ?? 0) > 0 ? (!empty($item['urgent']) ? 'bg-danger' : 'bg-primary') : 'bg-secondary' }}">
                                {{ $item['count'] ?? 0 }}
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
            @endif
            @if(auth()->user()->hasManagementAccess())
            <a href="{{ route('admin.dashboard') }}"
               class="btn btn-light btn-sm d-inline-flex align-items-center gap-1">
                <i class="bi bi-grid"></i>
                <span class="d-none d-sm-inline">Quản trị</span>
            </a>
            @endif
            <span class="today-label d-none d-md-flex align-items-center gap-1">
                <i class="bi bi-calendar3"></i>{{ now()->format('d.m.Y') }}
            </span>
            <span class="role-pill">{{ $portalRole ?? 'Nhân viên' }}</span>
        </div>
    </header>

    {{-- Main content --}}
    <main id="portal-main">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="status">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Đóng"></button>
        </div>
        @endif
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-exclamation-circle-fill me-2"></i>{{ session('error') }}
            <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Đóng"></button>
        </div>
        @endif
        @if($errors->any())
        <div class="alert alert-danger mb-4" role="alert">
            <strong><i class="bi bi-exclamation-triangle-fill me-2"></i>Vui lòng kiểm tra lại thông tin.</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
        @endif

        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="app-footer">
        <span>
            <i class="bi bi-person-circle me-1 text-primary"></i>
            <strong>Cổng Nhân Viên</strong> &mdash; Hệ thống quản lý nhân sự
        </span>
        <span>© {{ now()->year }}</span>
    </footer>

</div>{{-- /.app-shell --}}

<script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
@yield('scripts')
</body>
</html>
