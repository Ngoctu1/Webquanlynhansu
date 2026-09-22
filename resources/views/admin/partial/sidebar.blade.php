{{-- ================================================================
     SIDEBAR
     resources/views/admin/partial/sidebar.blade.php
     ================================================================ --}}
<nav id="sidebar">

    {{-- ===== LOGO / BRAND ===== --}}
    <div class="sidebar-brand">
        <div class="brand-logo">
            <i class="bi bi-people-fill"></i>
        </div>
        <div class="brand-text">
            <span class="brand-title">HRM System</span>
            <span class="brand-sub">Quản lý Nhân sự</span>
        </div>
    </div>

    {{-- ===== USER QUICK INFO ===== --}}
    

    {{-- ===== NAVIGATION MENU ===== --}}
    <div class="sidebar-scroll">
        <ul class="sidebar-menu">

            {{-- ---- Dashboard ---- --}}
            <li class="sidebar-label">TỔNG QUAN</li>

            <li class="nav-item">
                <a href="{{ url('/admin/dashboard') }}" class="nav-link active" id="menu-dashboard">
                    <i class="nav-icon bi bi-speedometer2"></i>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>

            {{-- ---- Quản lý nhân viên (có submenu) ---- --}}
            <li class="sidebar-label">QUẢN LÝ NHÂN SỰ</li>

            <li class="nav-item has-submenu">
                <a href="#submenu-nhanvien" class="nav-link nav-link-parent"
                   data-bs-toggle="collapse" aria-expanded="false" id="menu-nhanvien">
                    <i class="nav-icon bi bi-people"></i>
                    <span class="nav-text">Quản lý nhân viên</span>
                    <i class="nav-arrow bi bi-chevron-right"></i>
                </a>
                <ul class="collapse submenu" id="submenu-nhanvien">
                    <li>
                        <a href="{{ route('employees.index') }}" class="submenu-link" id="menu-ds-nhanvien">
                            <i class="bi bi-list-ul"></i> Danh sách nhân viên
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('employees.create') }}" class="submenu-link" id="menu-them-nhanvien">
                            <i class="bi bi-person-plus"></i> Thêm nhân viên
                        </a>
                    </li>
                </ul>
            </li>

            <li class="nav-item">
                <a href="{{ url('/admin/phong-ban') }}" class="nav-link" id="menu-phongban">
                    <i class="nav-icon bi bi-diagram-3"></i>
                    <span class="nav-text">Phòng ban</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ url('/admin/chuc-vu') }}" class="nav-link" id="menu-chucvu">
                    <i class="nav-icon bi bi-award"></i>
                    <span class="nav-text">Chức vụ</span>
                </a>
            </li>

            {{-- ---- Chấm công & Nghỉ phép ---- --}}
            <li class="sidebar-label">THỜI GIAN CÔNG VIỆC</li>

            <li class="nav-item">
                <a href="{{ url('/admin/cham-cong') }}" class="nav-link" id="menu-chamcong">
                    <i class="nav-icon bi bi-clock-history"></i>
                    <span class="nav-text">Chấm công</span>
                    <span class="nav-badge bg-warning">Mới</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ url('/admin/nghi-phep') }}" class="nav-link" id="menu-nghiphep">
                    <i class="nav-icon bi bi-calendar-check"></i>
                    <span class="nav-text">Nghỉ phép</span>
                    <span class="nav-badge bg-danger">3</span>
                </a>
            </li>

            {{-- ---- Tài chính ---- --}}
            <li class="sidebar-label">TÀI CHÍNH</li>

            <li class="nav-item">
                <a href="{{ url('/admin/luong-thuong') }}" class="nav-link" id="menu-luong">
                    <i class="nav-icon bi bi-cash-coin"></i>
                    <span class="nav-text">Lương &amp; thưởng</span>
                </a>
            </li>

            {{-- ---- Hợp đồng & Đánh giá ---- --}}
            <li class="sidebar-label">HỒ SƠ & ĐÁNH GIÁ</li>

            <li class="nav-item">
                <a href="{{ url('/admin/hop-dong') }}" class="nav-link" id="menu-hopdong">
                    <i class="nav-icon bi bi-file-earmark-text"></i>
                    <span class="nav-text">Hợp đồng lao động</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ url('/admin/danh-gia') }}" class="nav-link" id="menu-danhgia">
                    <i class="nav-icon bi bi-star-half"></i>
                    <span class="nav-text">Đánh giá nhân viên</span>
                </a>
            </li>

            {{-- ---- Báo cáo & Quản trị ---- --}}
            <li class="sidebar-label">HỆ THỐNG</li>

            <li class="nav-item">
                <a href="{{ url('/admin/bao-cao') }}" class="nav-link" id="menu-baocao">
                    <i class="nav-icon bi bi-bar-chart-line"></i>
                    <span class="nav-text">Báo cáo thống kê</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ url('/admin/tai-khoan') }}" class="nav-link" id="menu-taikhoan">
                    <i class="nav-icon bi bi-shield-person"></i>
                    <span class="nav-text">Quản lý tài khoản</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ url('/admin/cai-dat') }}" class="nav-link" id="menu-caidat">
                    <i class="nav-icon bi bi-gear"></i>
                    <span class="nav-text">Cài đặt</span>
                </a>
            </li>

        </ul>
    </div>{{-- /sidebar-scroll --}}

    {{-- ===== SIDEBAR FOOTER ===== --}}
    <div class="sidebar-footer">
        <div class="sidebar-footer-content">
            <i class="bi bi-info-circle"></i>
            <span class="sidebar-footer-text">v2.1.0 &bull; Build 2026</span>
        </div>
    </div>

</nav>{{-- /#sidebar --}}

{{-- ===== Sidebar Styles ===== --}}
<style>
    /* ---------- Brand ---------- */
    .sidebar-brand {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 18px 20px 14px 20px;
        border-bottom: 1px solid rgba(255,255,255,.07);
        overflow: hidden;
        flex-shrink: 0;
    }

    .brand-logo {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, #2563eb, #7c3aed);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 1.25rem;
        flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(37,99,235,.4);
    }

    .brand-text {
        display: flex;
        flex-direction: column;
        overflow: hidden;
        white-space: nowrap;
    }

    .brand-title {
        color: #f1f5f9;
        font-size: 0.95rem;
        font-weight: 700;
        letter-spacing: -0.02em;
    }

    .brand-sub {
        color: #64748b;
        font-size: 0.72rem;
        font-weight: 400;
    }

    /* ---------- User Quick Info ---------- */
    .sidebar-user {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 20px;
        overflow: hidden;
        border-bottom: 1px solid rgba(255,255,255,.07);
        flex-shrink: 0;
    }

    .sidebar-user-avatar {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: linear-gradient(135deg, #0ea5e9, #2563eb);
        color: #fff;
        font-size: 0.78rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .sidebar-user-info {
        display: flex;
        flex-direction: column;
        overflow: hidden;
        white-space: nowrap;
    }

    .sidebar-user-name {
        color: #e2e8f0;
        font-size: 0.82rem;
        font-weight: 600;
    }

    .sidebar-user-role {
        color: #64748b;
        font-size: 0.72rem;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    /* ---------- Divider ---------- */
    .sidebar-divider {
        height: 1px;
        background: rgba(255,255,255,.06);
        margin: 4px 0;
    }

    /* ---------- Scroll Area ---------- */
    .sidebar-scroll {
        flex: 1;
        min-height: 0;          /* Quan trọng: cho phép flex child co lại dưới 100% */
        overflow-y: auto;
        overflow-x: hidden;
        padding: 8px 0 12px 0;
    }

    .sidebar-scroll::-webkit-scrollbar { width: 4px; }
    .sidebar-scroll::-webkit-scrollbar-track { background: rgba(255,255,255,.04); }
    .sidebar-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,.18); border-radius: 2px; }
    .sidebar-scroll::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,.32); }

    /* ---------- Menu ---------- */
    .sidebar-menu {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    /* Section Labels */
    .sidebar-label {
        color: #475569;
        font-size: 0.65rem;
        font-weight: 700;
        letter-spacing: .08em;
        padding: 14px 20px 6px 20px;
        text-transform: uppercase;
        white-space: nowrap;
        overflow: hidden;
        transition: opacity .25s;
    }

    /* Nav Links */
    .nav-link {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 12px 10px 16px;
        margin: 2px 10px;
        border-radius: 10px;
        color: var(--sidebar-text);
        text-decoration: none;
        font-size: 0.875rem;
        font-weight: 500;
        white-space: nowrap;
        overflow: hidden;
        transition: background .2s, color .2s;
        position: relative;
    }

    .nav-link:hover {
        background: var(--sidebar-hover);
        color: #e2e8f0;
    }

    .nav-link.active {
        background: var(--sidebar-active);
        color: #fff;
        box-shadow: 0 4px 12px rgba(37,99,235,.35);
    }

    .nav-link.active .nav-icon { color: #fff; }

    .nav-icon {
        font-size: 1.05rem;
        flex-shrink: 0;
        width: 20px;
        text-align: center;
        transition: color .2s;
    }

    .nav-text {
        flex: 1;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Badges */
    .nav-badge {
        font-size: 0.65rem;
        font-weight: 700;
        padding: 2px 7px;
        border-radius: 20px;
        color: #fff;
        flex-shrink: 0;
        line-height: 1.5;
    }

    .nav-badge.bg-warning { background: #f59e0b !important; }
    .nav-badge.bg-danger  { background: #ef4444 !important; }

    /* Arrow for parent menus */
    .nav-arrow {
        font-size: 0.7rem;
        flex-shrink: 0;
        transition: transform .25s;
        color: #475569;
    }

    .nav-link-parent[aria-expanded="true"] .nav-arrow {
        transform: rotate(90deg);
    }

    .nav-link-parent[aria-expanded="true"] {
        color: #e2e8f0;
        background: var(--sidebar-hover);
    }

    /* Submenu */
    .submenu {
        list-style: none;
        padding: 4px 0 4px 0;
        margin: 0;
    }

    .submenu-link {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 12px 8px 52px;
        margin: 1px 10px;
        border-radius: 8px;
        color: #64748b;
        text-decoration: none;
        font-size: 0.825rem;
        font-weight: 400;
        white-space: nowrap;
        transition: background .2s, color .2s;
    }

    .submenu-link i { font-size: 0.8rem; }

    .submenu-link:hover {
        background: var(--sidebar-hover);
        color: #cbd5e1;
    }

    .submenu-link.active {
        color: #60a5fa;
        background: rgba(37,99,235,.15);
    }

    /* ---------- Sidebar Footer ---------- */
    .sidebar-footer {
        padding: 12px 20px;
        border-top: 1px solid rgba(255,255,255,.07);
        flex-shrink: 0;
    }

    .sidebar-footer-content {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #475569;
        font-size: 0.75rem;
        white-space: nowrap;
        overflow: hidden;
    }

    /* ===================================================================
       COLLAPSED STATE (desktop)
       - Labels hidden, text hidden, badge hidden, submenu shows as tooltip
       =================================================================== */
    #sidebar.collapsed .sidebar-label,
    #sidebar.collapsed .brand-text,
    #sidebar.collapsed .sidebar-user-info,
    #sidebar.collapsed .nav-text,
    #sidebar.collapsed .nav-arrow,
    #sidebar.collapsed .nav-badge,
    #sidebar.collapsed .sidebar-footer-content span {
        opacity: 0;
        width: 0;
        overflow: hidden;
    }

    #sidebar.collapsed .sidebar-brand { padding: 18px 14px 14px 14px; }
    #sidebar.collapsed .sidebar-user { padding: 14px; justify-content: center; }

    #sidebar.collapsed .nav-link {
        justify-content: center;
        padding: 10px 12px;
        margin: 2px 10px;
    }

    #sidebar.collapsed .nav-icon { width: auto; }

    #sidebar.collapsed .sidebar-footer-content { justify-content: center; }

    #sidebar.collapsed .submenu { display: none !important; }
</style>

{{-- ===== Sidebar Scripts ===== --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Auto-open submenu if a child link is active
        document.querySelectorAll('.submenu .submenu-link').forEach(function (link) {
            if (link.getAttribute('href') === window.location.pathname) {
                link.classList.add('active');
                const submenu = link.closest('.submenu');
                if (submenu) {
                    submenu.classList.add('show');
                    const parentLink = document.querySelector('[data-bs-target="#' + submenu.id + '"], [href="#' + submenu.id + '"]');
                    if (parentLink) parentLink.setAttribute('aria-expanded', 'true');
                }
            }
        });

        // Remove 'active' from main links if a submenu child is active
        const anyChildActive = document.querySelector('.submenu .submenu-link.active');
        if (anyChildActive) {
            document.querySelectorAll('.nav-link.active').forEach(function (el) {
                if (!el.classList.contains('nav-link-parent')) {
                    el.classList.remove('active');
                }
            });
        }
    });
</script>
