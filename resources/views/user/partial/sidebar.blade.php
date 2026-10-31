<aside class="portal-sidebar offcanvas-lg offcanvas-start" tabindex="-1" id="employeeSidebar" aria-labelledby="employeeSidebarLabel">
    <div class="portal-sidebar-head">
        <a class="portal-brand" href="{{ route('user.dashboard') }}"><span class="portal-brand-mark"><i class="bi bi-people" aria-hidden="true"></i></span><span><strong id="employeeSidebarLabel">HRM</strong><small>Employee Portal</small></span></a>
        <button type="button" class="btn-close btn-close-white d-lg-none" data-bs-dismiss="offcanvas" data-bs-target="#employeeSidebar" aria-label="Đóng menu điều hướng"></button>
    </div>
    <div class="offcanvas-body">
        <div class="portal-nav-label">KHÔNG GIAN CỦA BẠN</div>
        <nav class="portal-nav" aria-label="Điều hướng chính">
            <a class="portal-nav-link {{ request()->routeIs('user.dashboard') ? 'active' : '' }}" href="{{ route('user.dashboard') }}"><i class="bi bi-person-badge" aria-hidden="true"></i><span>Hồ sơ của tôi</span></a>
            <a class="portal-nav-link {{ request()->routeIs('user.contracts') ? 'active' : '' }}" href="{{ route('user.contracts') }}"><i class="bi bi-file-earmark-text" aria-hidden="true"></i><span>Hợp đồng lao động</span></a>
            <a class="portal-nav-link {{ request()->routeIs('user.leave-requests.*') ? 'active' : '' }}" href="{{ route('user.leave-requests.index') }}"><i class="bi bi-calendar2-check" aria-hidden="true"></i><span>Đăng ký nghỉ phép</span></a>
        </nav>
        <div class="portal-sidebar-bottom"><div class="portal-sidebar-person"><span class="portal-avatar" aria-hidden="true">{{ $portalInitials }}</span><div style="min-width: 0;"><span class="portal-person-name">{{ $portalName }}</span><small>{{ $portalRole ?: 'Cổng thông tin nhân viên' }}</small></div></div></div>
    </div>
</aside>
