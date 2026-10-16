<header class="portal-header">
    <div class="d-flex align-items-center gap-3" style="min-width: 0;">
        <button class="portal-menu-toggle d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#employeeSidebar" aria-controls="employeeSidebar" aria-label="Mở menu điều hướng">
            <i class="bi bi-list" aria-hidden="true"></i>
        </button>
        <nav aria-label="Đường dẫn trang">
            <ol class="portal-breadcrumb">
                <li class="d-none d-md-block"><a href="{{ route('user.attendances') }}">Cổng nhân viên</a></li>
                <li class="d-none d-md-block" aria-hidden="true"><i class="bi bi-chevron-right" style="font-size: .6rem;"></i></li>
                <li aria-current="page">@yield('title', 'Hồ sơ của tôi')</li>
            </ol>
        </nav>
    </div>
    <div class="dropdown flex-shrink-0">
        <button class="portal-account-button" type="button" id="portalAccountMenu" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Mở menu tài khoản của {{ $portalName }}">
            <span class="portal-avatar" aria-hidden="true">{{ $portalInitials }}</span>
            <span class="portal-account-meta d-none d-md-block">
                <span class="portal-person-name">{{ $portalName }}</span>
                <small>{{ $portalRole ?: 'Tài khoản của tôi' }}</small>
            </span>
            <i class="bi bi-chevron-down ms-1" style="font-size: .65rem;" aria-hidden="true"></i>
        </button>
        <div class="dropdown-menu dropdown-menu-end portal-account-dropdown" aria-labelledby="portalAccountMenu">
            <div class="px-3 py-2">
                <div class="portal-person-name">{{ $portalName }}</div>
                <div class="small portal-muted text-break">{{ $portalUser->email }}</div>
            </div>
            <hr class="dropdown-divider">
            <a class="dropdown-item" href="{{ route('user.dashboard') }}"><i class="bi bi-person-circle" aria-hidden="true"></i>Hồ sơ của tôi</a>
            @if(auth()->user()->canManageHrm())
                <a class="dropdown-item" href="{{ route('admin.dashboard') }}"><i class="bi bi-grid" aria-hidden="true"></i>Trang quản trị</a>
            @endif
            <hr class="dropdown-divider">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right" aria-hidden="true"></i>Đăng xuất</button>
            </form>
        </div>
    </div>
</header>
