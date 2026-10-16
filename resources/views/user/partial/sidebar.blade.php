<aside class="portal-sidebar offcanvas-lg offcanvas-start" tabindex="-1" id="employeeSidebar" aria-labelledby="employeeSidebarLabel">
    <div class="portal-sidebar-head">
        <a class="portal-brand" href="{{ route('user.attendances') }}">
            <span class="portal-brand-mark"><i class="bi bi-people" aria-hidden="true"></i></span>
            <span><strong id="employeeSidebarLabel">HRM</strong><small>Employee Portal</small></span>
        </a>
        <button type="button" class="btn-close btn-close-white d-lg-none" data-bs-dismiss="offcanvas" data-bs-target="#employeeSidebar" aria-label="Đóng menu điều hướng"></button>
    </div>
    <div class="offcanvas-body">
        <div class="portal-nav-label">KHÔNG GIAN CỦA BẠN</div>
        <nav class="portal-nav" aria-label="Điều hướng chính">
            <a class="portal-nav-link {{ request()->routeIs('user.dashboard') ? 'active' : '' }}" href="{{ route('user.dashboard') }}" @if(request()->routeIs('user.dashboard')) aria-current="page" @endif>
                <i class="bi bi-person-badge" aria-hidden="true"></i><span>Hồ sơ của tôi</span>
            </a>
            <a class="portal-nav-link {{ request()->routeIs('user.contracts') ? 'active' : '' }}" href="{{ route('user.contracts') }}" @if(request()->routeIs('user.contracts')) aria-current="page" @endif>
                <i class="bi bi-file-earmark-text" aria-hidden="true"></i><span>Hợp đồng lao động</span>
            </a>
            <a class="portal-nav-link {{ request()->routeIs('user.attendances') ? 'active' : '' }}" href="{{ route('user.attendances') }}" @if(request()->routeIs('user.attendances')) aria-current="page" @endif>
                <i class="bi bi-calendar-check" aria-hidden="true"></i><span>Chấm công</span>
            </a>
            <a class="portal-nav-link {{ request()->routeIs('user.attendances.history') ? 'active' : '' }}" href="{{ route('user.attendances.history') }}" @if(request()->routeIs('user.attendances.history')) aria-current="page" @endif>
                <i class="bi bi-clock-history" aria-hidden="true"></i><span>Lịch sử chấm công</span>
            </a>
            <a class="portal-nav-link {{ request()->routeIs('user.payrolls') ? 'active' : '' }}" href="{{ route('user.payrolls') }}" @if(request()->routeIs('user.payrolls')) aria-current="page" @endif>
                <i class="bi bi-wallet2" aria-hidden="true"></i><span>Bảng lương</span>
            </a>
        </nav>
        <div class="portal-sidebar-bottom">
            <div class="portal-sidebar-person">
                <span class="portal-avatar" aria-hidden="true">{{ $portalInitials }}</span>
                <div style="min-width: 0;">
                    <span class="portal-person-name">{{ $portalName }}</span>
                    <small>{{ $portalRole ?: 'Cổng thông tin nhân viên' }}</small>
                </div>
            </div>
        </div>
    </div>
</aside>
