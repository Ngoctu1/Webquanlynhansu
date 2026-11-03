<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#124b45">
    <title>@yield('title', 'Tổng quan') — Nhân Sự</title>
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/extra.css') }}">
    @yield('styles')
</head>
<body>
<a class="visually-hidden-focusable skip-link" href="#main-content">Đi đến nội dung</a>

<aside class="sidebar offcanvas-lg offcanvas-start" tabindex="-1" id="sidebar" aria-label="Điều hướng chính">
    <a class="brand" href="{{ route('admin.dashboard') }}">
        <span class="brand-icon"><i class="bi bi-intersect"></i></span>
        <span>nhân sự<span class="brand-caption">PEOPLE &amp; OPERATIONS</span></span>
    </a>

    <div class="workspace-label">
        <span class="workspace-dot"></span> Không gian làm việc <span class="ms-auto small">01</span>
    </div>

    <div class="nav-label">QUẢN LÝ NHÂN SỰ</div>
    <nav class="sidebar-nav">
        <a class="{{ request()->routeIs('admin.*') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
            <i class="bi bi-grid-1x2"></i>Tổng quan</a>
        <a class="{{ request()->routeIs('employees.*') ? 'active' : '' }}" href="{{ route('employees.index') }}">
            <i class="bi bi-people"></i>Nhân viên</a>
        <a class="{{ request()->routeIs('contracts.*') ? 'active' : '' }}" href="{{ route('contracts.index') }}">
            <i class="bi bi-file-earmark-text"></i>Hợp đồng</a>
        @if(auth()->user()->hasManagementAccess())
        <a class="{{ request()->routeIs('leave-requests.*') ? 'active' : '' }}" href="{{ route('leave-requests.index') }}">
            <i class="bi bi-check2-square"></i>Quản lý nghỉ phép</a>
        @endif
        @if(auth()->user()->isDirector())
        <a class="{{ request()->routeIs('change-requests.*') ? 'active' : '' }}" href="{{ route('change-requests.index') }}">
            <i class="bi bi-clipboard2-check"></i>Duyệt đề xuất HR
            @if(($notificationCounts['pending_hr_proposals'] ?? 0) > 0)
                <span class="badge bg-danger ms-auto">{{ $notificationCounts['pending_hr_proposals'] }}</span>
            @endif
        </a>
        @endif

        <div class="nav-label">DANH MỤC &amp; HỆ THỐNG</div>
        <a class="{{ request()->routeIs('departments.*') ? 'active' : '' }}" href="{{ route('departments.index') }}">
            <i class="bi bi-building"></i>Phòng ban</a>
        <a class="{{ request()->routeIs('positions.*') ? 'active' : '' }}" href="{{ route('positions.index') }}">
            <i class="bi bi-briefcase"></i>Chức vụ</a>
        <a class="{{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
            <i class="bi bi-shield-person"></i>Tài khoản</a>
        @can('view-audit-logs')
        <a class="{{ request()->routeIs('audit.*') ? 'active' : '' }}" href="{{ route('audit.index') }}">
            <i class="bi bi-clock-history"></i>Lịch sử thao tác</a>
        @endcan

        <div class="nav-label">CÁ NHÂN</div>
        <a class="{{ request()->routeIs('user.*') ? 'active' : '' }}" href="{{ route('user.dashboard') }}">
            <i class="bi bi-person"></i>Hồ sơ của tôi</a>
    </nav>

    <div class="sidebar-note">
        <i class="bi bi-shield-check"></i>
        <strong>Thông tin có kiểm soát</strong>
        <p>Mọi thay đổi đều có lịch sử.<br>Mọi phê duyệt đều rõ ràng.</p>
    </div>

    <div class="sidebar-user">
        <span class="avatar avatar-light">{{ mb_substr(auth()->user()->employee?->full_name ?? auth()->user()->email, 0, 1) }}</span>
        <div>
            <strong>{{ auth()->user()->employee?->full_name ?? auth()->user()->email }}</strong>
            <small>{{ auth()->user()->role->name }}</small>
        </div>
        <form method="POST" action="{{ route('logout') }}" class="ms-auto">
            @csrf
            <button class="logout-btn" aria-label="Đăng xuất" title="Đăng xuất">
                <i class="bi bi-box-arrow-right"></i>
            </button>
        </form>
    </div>
</aside>

<div class="app-shell">
    <header class="topbar">
        <div class="d-flex align-items-center gap-3">
            <button type="button" class="btn btn-light d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-label="Mở menu">
                <i class="bi bi-list"></i>
            </button>
            <span class="text-muted">Không gian nhân sự</span>
            <i class="bi bi-chevron-right small text-muted"></i>
            <strong>@yield('title', 'Tổng quan')</strong>
        </div>
        <div class="topbar-right">
            @if(!empty($notificationCounts['items']))
            <div class="dropdown">
                <button class="btn btn-light btn-sm position-relative d-inline-flex align-items-center gap-1" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-bell"></i>
                    <span class="d-none d-md-inline">Việc cần xử lý</span>
                    @if(($notificationCounts['total_badges'] ?? 0) > 0)
                        <span class="badge rounded-pill bg-danger">{{ $notificationCounts['total_badges'] }}</span>
                    @endif
                </button>
                <div class="dropdown-menu dropdown-menu-end shadow border-0 p-2" style="min-width: 320px;">
                    <div class="px-2 py-1 border-bottom mb-1 fw-bold small text-uppercase text-muted">
                        Thông báo &amp; Việc cần xử lý ({{ $notificationCounts['role'] ?? '' }})
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
            @if(auth()->user()->hasRole(\App\Models\Role::HR, 'Director'))
            <a href="{{ route('user.leave-requests.index') }}" class="btn btn-light btn-sm d-inline-flex align-items-center gap-1">
                <i class="bi bi-calendar2-check"></i><span class="d-none d-sm-inline">Nghỉ phép cá nhân</span>
            </a>
            @endif
            <span class="today-label"><i class="bi bi-calendar3 me-2"></i>{{ now()->format('d.m.Y') }}</span>
            <span class="role-pill">{{ auth()->user()->role->name }}</span>
        </div>
    </header>

    <main id="main-content">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="status">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Đóng"></button>
        </div>
        @endif
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
            <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Đóng"></button>
        </div>
        @endif
        @if($errors->any())
        <div class="alert alert-danger" role="alert">
            <strong>Vui lòng kiểm tra lại thông tin.</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
        @endif
        @yield('content')
    </main>

    <footer class="app-footer">
        <span>Nhân Sự <span class="mx-2">/</span> Quản lý hồ sơ &amp; phê duyệt</span>
        <span>Đồ án thực hành © {{ now()->year }}</span>
    </footer>
</div>

{{-- Confirm Modal --}}
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmTitle">Xác nhận thao tác</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body" id="confirmMessage"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Quay lại</button>
                <button type="button" class="btn btn-primary" id="confirmSubmit">Xác nhận</button>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('js/app.js') }}"></script>
@yield('scripts')
</body>
</html>
