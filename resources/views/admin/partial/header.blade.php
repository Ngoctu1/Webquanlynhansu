{{-- ================================================================
     HEADER / NAVBAR
     resources/views/admin/partial/header.blade.php
     ================================================================ --}}
<header id="header">
    <div class="d-flex align-items-center h-100 px-3 px-md-4">

        {{-- ===== LEFT: Toggle & Brand ===== --}}
        <div class="d-flex align-items-center gap-3">

            {{-- Desktop sidebar toggle --}}
            <button class="btn btn-icon d-none d-lg-flex" onclick="toggleSidebar()" title="Thu gọn / Mở rộng menu"
                    id="btn-desktop-toggle">
                <i class="bi bi-layout-sidebar fs-5 text-secondary"></i>
            </button>

            {{-- Mobile sidebar toggle --}}
            <button class="btn btn-icon d-flex d-lg-none" onclick="openMobileSidebar()" title="Mở menu"
                    id="btn-mobile-toggle">
                <i class="bi bi-list fs-4 text-secondary"></i>
            </button>

            {{-- Brand / System Name --}}
            <div class="header-brand d-none d-md-flex align-items-center gap-2">
                <div class="brand-dot"></div>
                <span class="fw-600 text-primary-brand">Hệ thống Quản lý Nhân sự</span>
            </div>
        </div>

        {{-- ===== CENTER: Search ===== --}}
        <div class="header-search mx-auto d-none d-md-block">
            <div class="search-wrapper">
                <i class="bi bi-search search-icon"></i>
                <input type="search"
                       class="form-control search-input"
                       placeholder="Tìm kiếm nhân viên, phòng ban..."
                       id="header-search-input"
                       autocomplete="off">
                <kbd class="search-shortcut d-none d-lg-flex">Ctrl K</kbd>
            </div>
        </div>

        {{-- ===== RIGHT: Actions ===== --}}
        <div class="d-flex align-items-center gap-2 ms-auto ms-md-0">

            {{-- Mobile search --}}
            <button class="btn btn-icon d-flex d-md-none" id="btn-mobile-search" title="Tìm kiếm">
                <i class="bi bi-search fs-5 text-secondary"></i>
            </button>

            {{-- Notifications --}}
            <div class="dropdown" id="dropdown-notifications">
                <button class="btn btn-icon position-relative" data-bs-toggle="dropdown" aria-expanded="false"
                        title="Thông báo" id="btn-notifications">
                    <i class="bi bi-bell fs-5 text-secondary"></i>
                    <span class="notif-badge">5</span>
                </button>
                <div class="dropdown-menu dropdown-menu-end notif-dropdown shadow-lg">
                    <div class="notif-header d-flex align-items-center justify-content-between px-3 py-2">
                        <span class="fw-600">Thông báo</span>
                        <a href="#" class="text-primary small fw-500">Đánh dấu tất cả đã đọc</a>
                    </div>
                    <div class="notif-divider"></div>

                    {{-- Notification Items --}}
                    <div class="notif-list">
                        <a href="#" class="notif-item unread d-flex gap-3 px-3 py-3">
                            <div class="notif-avatar bg-primary-soft text-primary">
                                <i class="bi bi-person-plus"></i>
                            </div>
                            <div class="flex-1">
                                <p class="notif-text mb-0">Nhân viên <strong>Nguyễn Văn A</strong> vừa được thêm vào hệ thống.</p>
                                <span class="notif-time">2 phút trước</span>
                            </div>
                            <div class="notif-dot"></div>
                        </a>

                        <a href="#" class="notif-item unread d-flex gap-3 px-3 py-3">
                            <div class="notif-avatar bg-warning-soft text-warning">
                                <i class="bi bi-calendar-x"></i>
                            </div>
                            <div class="flex-1">
                                <p class="notif-text mb-0"><strong>Trần Thị B</strong> đã gửi yêu cầu nghỉ phép.</p>
                                <span class="notif-time">15 phút trước</span>
                            </div>
                            <div class="notif-dot"></div>
                        </a>

                        <a href="#" class="notif-item unread d-flex gap-3 px-3 py-3">
                            <div class="notif-avatar bg-success-soft text-success">
                                <i class="bi bi-file-earmark-check"></i>
                            </div>
                            <div class="flex-1">
                                <p class="notif-text mb-0">Hợp đồng của <strong>Lê Văn C</strong> sắp hết hạn (5 ngày).</p>
                                <span class="notif-time">1 giờ trước</span>
                            </div>
                            <div class="notif-dot"></div>
                        </a>

                        <a href="#" class="notif-item d-flex gap-3 px-3 py-3">
                            <div class="notif-avatar bg-danger-soft text-danger">
                                <i class="bi bi-exclamation-circle"></i>
                            </div>
                            <div class="flex-1">
                                <p class="notif-text mb-0">Phát hiện bất thường chấm công tháng 9.</p>
                                <span class="notif-time">3 giờ trước</span>
                            </div>
                        </a>

                        <a href="#" class="notif-item d-flex gap-3 px-3 py-3">
                            <div class="notif-avatar bg-info-soft text-info">
                                <i class="bi bi-cash-coin"></i>
                            </div>
                            <div class="flex-1">
                                <p class="notif-text mb-0">Bảng lương tháng 8 đã được duyệt và xuất file.</p>
                                <span class="notif-time">Hôm qua</span>
                            </div>
                        </a>
                    </div>

                    <div class="notif-divider"></div>
                    <div class="text-center py-2">
                        <a href="#" class="small fw-500 text-primary">Xem tất cả thông báo <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="dropdown d-none d-sm-block" id="dropdown-quick-actions">
                <button class="btn btn-icon" data-bs-toggle="dropdown" aria-expanded="false"
                        title="Thao tác nhanh" id="btn-quick-actions">
                    <i class="bi bi-grid-3x3-gap fs-5 text-secondary"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end quick-actions-dropdown shadow-lg p-3">
                    <p class="small text-secondary fw-600 mb-2 px-1">THAO TÁC NHANH</p>
                    <div class="row g-2">
                        <div class="col-4">
                            <a href="#" class="quick-action-btn text-center d-flex flex-column align-items-center gap-1 p-2 rounded-3">
                                <i class="bi bi-person-plus-fill fs-5 text-primary"></i>
                                <span class="small">Thêm NV</span>
                            </a>
                        </div>
                        <div class="col-4">
                            <a href="#" class="quick-action-btn text-center d-flex flex-column align-items-center gap-1 p-2 rounded-3">
                                <i class="bi bi-clock-history fs-5 text-warning"></i>
                                <span class="small">Chấm công</span>
                            </a>
                        </div>
                        <div class="col-4">
                            <a href="#" class="quick-action-btn text-center d-flex flex-column align-items-center gap-1 p-2 rounded-3">
                                <i class="bi bi-calendar-check fs-5 text-success"></i>
                                <span class="small">Nghỉ phép</span>
                            </a>
                        </div>
                        <div class="col-4">
                            <a href="#" class="quick-action-btn text-center d-flex flex-column align-items-center gap-1 p-2 rounded-3">
                                <i class="bi bi-cash-stack fs-5 text-info"></i>
                                <span class="small">Lương</span>
                            </a>
                        </div>
                        <div class="col-4">
                            <a href="#" class="quick-action-btn text-center d-flex flex-column align-items-center gap-1 p-2 rounded-3">
                                <i class="bi bi-file-earmark-text fs-5 text-danger"></i>
                                <span class="small">Báo cáo</span>
                            </a>
                        </div>
                        <div class="col-4">
                            <a href="#" class="quick-action-btn text-center d-flex flex-column align-items-center gap-1 p-2 rounded-3">
                                <i class="bi bi-bar-chart-line fs-5 text-purple"></i>
                                <span class="small">Thống kê</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Divider --}}
            <div class="vr opacity-25 mx-1 d-none d-sm-block" style="height:24px;"></div>

            {{-- User Account Dropdown --}}
            <div class="dropdown" id="dropdown-user">
                <button class="btn d-flex align-items-center gap-2 user-menu-btn px-2"
                        data-bs-toggle="dropdown" aria-expanded="false" id="btn-user-menu">
                    <div class="user-avatar">
                        <span>AD</span>
                    </div>
                    <div class="user-info d-none d-lg-block text-start">
                        <p class="user-name mb-0">Admin HRM</p>
                        <p class="user-role mb-0">Quản trị viên</p>
                    </div>
                    <i class="bi bi-chevron-down small text-secondary d-none d-lg-block"></i>
                </button>

                <ul class="dropdown-menu dropdown-menu-end shadow user-dropdown mt-1">
                    <li>
                        <div class="px-3 pt-2 pb-3 border-bottom">
                            <div class="d-flex align-items-center gap-3">
                                <div class="user-avatar-lg">AD</div>
                                <div>
                                    <p class="fw-600 mb-0">Admin HRM</p>
                                    <p class="small text-secondary mb-0">admin@hrm.vn</p>
                                    <span class="badge bg-primary-soft text-primary small">Quản trị viên</span>
                                </div>
                            </div>
                        </div>
                    </li>
                    <li><a class="dropdown-item d-flex align-items-center gap-2 py-2" href="#">
                        <i class="bi bi-person-circle text-primary"></i> Hồ sơ cá nhân
                    </a></li>
                    <li><a class="dropdown-item d-flex align-items-center gap-2 py-2" href="#">
                        <i class="bi bi-gear text-secondary"></i> Cài đặt tài khoản
                    </a></li>
                    <li><a class="dropdown-item d-flex align-items-center gap-2 py-2" href="#">
                        <i class="bi bi-shield-lock text-warning"></i> Bảo mật
                    </a></li>
                    <li><hr class="dropdown-divider my-1"></li>
                    <li><a class="dropdown-item d-flex align-items-center gap-2 py-2 text-danger" href="#">
                        <i class="bi bi-box-arrow-right"></i> Đăng xuất
                    </a></li>
                </ul>
            </div>

        </div>{{-- /RIGHT --}}
    </div>{{-- /container-fluid --}}
</header>

{{-- ===== Mobile Search Bar ===== --}}
<div id="mobile-search-bar" class="d-md-none px-3 py-2 bg-white border-bottom" style="display:none!important;">
    <div class="search-wrapper">
        <i class="bi bi-search search-icon"></i>
        <input type="search" class="form-control search-input" placeholder="Tìm kiếm..." autocomplete="off">
    </div>
</div>

{{-- ===== Header Styles ===== --}}
<style>
    /* ---------- Base ---------- */
    #header {
        display: flex;
        align-items: center;
    }

    #header .d-flex.align-items-center.h-100 {
        width: 100%;
    }

    /* ---------- Icon Buttons ---------- */
    .btn-icon {
        width: 38px;
        height: 38px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        background: transparent;
        border: none;
        transition: background .2s, color .2s;
        color: var(--text-secondary);
    }

    .btn-icon:hover {
        background: var(--body-bg);
        color: var(--primary-color);
    }

    /* ---------- Brand ---------- */
    .brand-dot {
        width: 10px;
        height: 10px;
        background: var(--primary-color);
        border-radius: 50%;
        box-shadow: 0 0 0 3px var(--primary-light);
    }

    .text-primary-brand {
        color: var(--text-primary);
        font-size: 0.95rem;
        letter-spacing: -0.01em;
    }

    /* ---------- Search ---------- */
    .header-search {
        width: 100%;
        max-width: 420px;
    }

    .search-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }

    .search-icon {
        position: absolute;
        left: 14px;
        color: var(--text-secondary);
        font-size: 0.875rem;
        pointer-events: none;
    }

    .search-input {
        padding-left: 40px;
        padding-right: 76px;
        height: 40px;
        border-radius: 10px;
        border: 1.5px solid var(--border-color);
        background: var(--body-bg);
        font-size: 0.875rem;
        width: 100%;
        transition: border-color .2s, box-shadow .2s;
        color: var(--text-primary);
    }

    .search-input:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(37,99,235,.1);
        background: #fff;
    }

    .search-input::placeholder { color: #a0aec0; }

    .search-shortcut {
        position: absolute;
        right: 10px;
        background: #e2e8f0;
        color: var(--text-secondary);
        border: none;
        border-radius: 6px;
        font-size: 0.7rem;
        padding: 2px 7px;
        display: flex;
        align-items: center;
        font-family: inherit;
    }

    /* ---------- Notifications ---------- */
    .notif-badge {
        position: absolute;
        top: 5px;
        right: 5px;
        width: 18px;
        height: 18px;
        background: #ef4444;
        color: #fff;
        border-radius: 50%;
        font-size: 0.65rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid #fff;
        line-height: 1;
    }

    .notif-dropdown {
        width: 360px;
        max-width: calc(100vw - 32px);
        border-radius: 16px;
        border: 1px solid var(--border-color);
        padding: 0;
        overflow: hidden;
    }

    .notif-header { background: #f8fafc; }

    .notif-divider { height: 1px; background: var(--border-color); }

    .notif-list { max-height: 320px; overflow-y: auto; }

    .notif-item {
        text-decoration: none;
        color: var(--text-primary);
        transition: background .15s;
        position: relative;
        border-bottom: 1px solid #f1f5f9;
    }

    .notif-item:last-child { border-bottom: none; }
    .notif-item:hover { background: #f8fafc; }
    .notif-item.unread { background: #eff6ff; }
    .notif-item.unread:hover { background: #dbeafe; }

    .notif-avatar {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    .notif-text { font-size: 0.82rem; line-height: 1.45; }
    .notif-time { font-size: 0.75rem; color: var(--text-secondary); margin-top: 2px; display: block; }

    .notif-dot {
        width: 8px;
        height: 8px;
        background: var(--primary-color);
        border-radius: 50%;
        flex-shrink: 0;
        margin-top: 6px;
    }

    /* ---------- Quick Actions ---------- */
    .quick-actions-dropdown {
        width: 220px;
        border-radius: 16px;
        border: 1px solid var(--border-color);
    }

    .quick-action-btn {
        text-decoration: none;
        color: var(--text-primary);
        font-size: 0.78rem;
        border-radius: 10px !important;
        transition: background .15s;
        line-height: 1.3;
    }

    .quick-action-btn:hover { background: var(--body-bg); color: var(--primary-color); }
    .text-purple { color: #7c3aed; }

    /* ---------- Soft colors ---------- */
    .bg-primary-soft { background: #dbeafe; }
    .bg-warning-soft  { background: #fef3c7; }
    .bg-success-soft  { background: #d1fae5; }
    .bg-danger-soft   { background: #fee2e2; }
    .bg-info-soft     { background: #dbeafe; }

    /* ---------- User Menu ---------- */
    .user-menu-btn {
        border: none;
        background: transparent;
        border-radius: 12px;
        padding: 6px 8px;
        transition: background .2s;
    }

    .user-menu-btn:hover { background: var(--body-bg); }
    .user-menu-btn:focus { box-shadow: none; }

    .user-avatar {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: linear-gradient(135deg, #2563eb, #7c3aed);
        color: #fff;
        font-size: 0.78rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .user-avatar-lg {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: linear-gradient(135deg, #2563eb, #7c3aed);
        color: #fff;
        font-size: 0.9rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .user-name  { font-size: 0.85rem; font-weight: 600; color: var(--text-primary); }
    .user-role  { font-size: 0.72rem; color: var(--text-secondary); }

    .user-dropdown {
        min-width: 240px;
        border-radius: 16px;
        border: 1px solid var(--border-color);
        padding: 0;
        overflow: hidden;
    }

    .user-dropdown .dropdown-item {
        font-size: 0.875rem;
        padding: 10px 16px;
        transition: background .15s;
        border-radius: 0;
    }

    .user-dropdown .dropdown-item:hover { background: var(--body-bg); }

    .fw-600 { font-weight: 600; }
    .fw-500 { font-weight: 500; }
    .flex-1 { flex: 1; min-width: 0; }
</style>

{{-- ===== Header Scripts ===== --}}
<script>
    // Mobile search toggle
    document.getElementById('btn-mobile-search').addEventListener('click', function () {
        const bar = document.getElementById('mobile-search-bar');
        bar.style.display = (bar.style.display === 'none' || bar.style.display === '') ? 'block' : 'none';
    });

    // Ctrl+K shortcut
    document.addEventListener('keydown', function (e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            const input = document.getElementById('header-search-input');
            if (input) input.focus();
        }
    });
</script>
