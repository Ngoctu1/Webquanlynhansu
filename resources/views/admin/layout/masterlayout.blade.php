<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Hệ thống Quản lý Nhân sự - Giải pháp quản trị nhân sự toàn diện">

    <title>@yield('title', 'Dashboard') | Quản lý Nhân sự</title>

    {{-- Bootstrap 5 CSS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

    {{-- Bootstrap Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    {{-- Google Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    {{-- Custom Layout Styles --}}
    <style>
        /* ===== RESET & BASE ===== */
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --sidebar-width: 260px;
            --sidebar-collapsed-width: 72px;
            --header-height: 64px;
            --primary-color: #2563eb;
            --primary-dark: #1d4ed8;
            --primary-light: #dbeafe;
            --sidebar-bg: #0f172a;
            --sidebar-text: #94a3b8;
            --sidebar-hover: #1e293b;
            --sidebar-active: #2563eb;
            --header-bg: #ffffff;
            --body-bg: #f1f5f9;
            --card-bg: #ffffff;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --border-color: #e2e8f0;
            --shadow-sm: 0 1px 3px rgba(0,0,0,.08), 0 1px 2px rgba(0,0,0,.06);
            --shadow-md: 0 4px 6px -1px rgba(0,0,0,.1), 0 2px 4px -1px rgba(0,0,0,.06);
            --shadow-lg: 0 10px 15px -3px rgba(0,0,0,.1), 0 4px 6px -2px rgba(0,0,0,.05);
            --radius: 12px;
            --transition: all 0.25s ease;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--body-bg);
            color: var(--text-primary);
            overflow-x: hidden;
        }

        /* ===== WRAPPER ===== */
        #app-wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* ===== SIDEBAR ===== */
        #sidebar {
            width: var(--sidebar-width);
            height: 100vh;          /* Chiều cao cố định theo viewport */
            background: var(--sidebar-bg);
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1050;
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            overflow: hidden;       /* Sidebar tổng thể không cuộn — chỉ .sidebar-scroll cuộn */
        }

        #sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }

        /* ===== MAIN CONTENT AREA ===== */
        #main-content {
            margin-left: var(--sidebar-width);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            transition: var(--transition);
        }

        #main-content.expanded {
            margin-left: var(--sidebar-collapsed-width);
        }

        /* ===== HEADER ===== */
        #header {
            height: var(--header-height);
            background: var(--header-bg);
            position: sticky;
            top: 0;
            z-index: 1040;
            border-bottom: 1px solid var(--border-color);
            box-shadow: var(--shadow-sm);
        }

        /* ===== PAGE CONTENT ===== */
        #page-content {
            flex: 1;
            padding: 28px 28px 0 28px;
        }

        /* ===== FOOTER ===== */
        #footer {
            margin-top: auto;
            padding: 16px 28px;
            background: var(--header-bg);
            border-top: 1px solid var(--border-color);
        }

        /* ===== OVERLAY (mobile) ===== */
        #sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.55);
            z-index: 1049;
        }

        /* ===== SCROLLBAR ===== */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 991.98px) {
            #sidebar {
                left: calc(-1 * var(--sidebar-width));
            }

            #sidebar.mobile-open {
                left: 0;
            }

            #main-content {
                margin-left: 0 !important;
            }

            #sidebar-overlay.active {
                display: block;
            }

            #page-content {
                padding: 20px 16px 0 16px;
            }
        }

        @media (max-width: 575.98px) {
            #page-content {
                padding: 16px 12px 0 12px;
            }
        }
    </style>

    {{-- Additional page-specific styles --}}
    @yield('styles')
</head>
<body>

{{-- Sidebar Overlay (mobile) --}}
<div id="sidebar-overlay" onclick="closeMobileSidebar()"></div>

<div id="app-wrapper">

    {{-- ===== SIDEBAR ===== --}}
    @include('admin.partial.sidebar')

    {{-- ===== MAIN CONTENT ===== --}}
    <div id="main-content">

        {{-- Header --}}
        @include('admin.partial.header')

        {{-- Page Content --}}
        <div id="page-content">
            @yield('content')
        </div>

        {{-- Footer --}}
        @include('admin.partial.footer')

    </div>{{-- /#main-content --}}

</div>{{-- /#app-wrapper --}}

{{-- Bootstrap 5 JS Bundle (includes Popper) --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

{{-- Chart.js CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

{{-- Layout Core Script --}}
<script>
    /* ---------- Sidebar Toggle (Desktop) ---------- */
    function toggleSidebar() {
        const sidebar    = document.getElementById('sidebar');
        const mainContent = document.getElementById('main-content');
        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('expanded');

        // Save state
        const isCollapsed = sidebar.classList.contains('collapsed');
        localStorage.setItem('sidebarCollapsed', isCollapsed);
    }

    /* ---------- Mobile Sidebar ---------- */
    function openMobileSidebar() {
        document.getElementById('sidebar').classList.add('mobile-open');
        document.getElementById('sidebar-overlay').classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeMobileSidebar() {
        document.getElementById('sidebar').classList.remove('mobile-open');
        document.getElementById('sidebar-overlay').classList.remove('active');
        document.body.style.overflow = '';
    }

    /* ---------- Restore sidebar state on load ---------- */
    document.addEventListener('DOMContentLoaded', function () {
        const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        if (isCollapsed && window.innerWidth >= 992) {
            document.getElementById('sidebar').classList.add('collapsed');
            document.getElementById('main-content').classList.add('expanded');
        }

        // Highlight active menu item based on current URL
        const currentPath = window.location.pathname;
        document.querySelectorAll('#sidebar .nav-link').forEach(function (link) {
            if (link.getAttribute('href') === currentPath) {
                link.classList.add('active');
            }
        });
    });
</script>

{{-- Additional page-specific scripts --}}
@yield('scripts')

</body>
</html>
