<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Hồ sơ cá nhân') | HRM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        /* All portal styles live under this body class. */
        .employee-portal {
            --portal-navy: #15243c;
            --portal-blue: #245bd6;
            --portal-muted: #637086;
            --portal-line: #e6ebf2;
            --portal-radius: 16px;
            --bs-body-color: #24334b;
            --bs-primary-rgb: 36, 91, 214;
            margin: 0;
            background: #f4f6fa;
            color: #24334b;
            font-family: system-ui, -apple-system, "Segoe UI", sans-serif;
            font-size: .9375rem;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }
        .employee-portal a { text-underline-offset: 3px; }
        .employee-portal :is(a, button, input, select, summary, [tabindex]):focus-visible {
            outline: 3px solid #668fee;
            outline-offset: 4px;
            box-shadow: none;
        }
        .employee-portal .portal-skip-link {
            position: fixed; top: 12px; left: 12px; z-index: 1100;
            padding: 10px 16px; border-radius: 8px; background: #fff; color: #1648aa;
            transform: translateY(-180%);
        }
        .employee-portal .portal-skip-link:focus { transform: translateY(0); }
        .employee-portal .portal-muted { color: var(--portal-muted); }
        .employee-portal .portal-eyebrow {
            margin-bottom: 7px; color: var(--portal-muted); font-size: .7rem;
            font-weight: 700; letter-spacing: .1em; text-transform: uppercase;
        }
        .employee-portal .portal-workspace {
            min-width: 0; min-height: 100vh; min-height: 100dvh;
            display: flex; flex-direction: column;
        }
        .employee-portal .portal-main {
            width: 100%; max-width: 1540px; min-width: 0;
            flex: 1 0 auto; margin-inline: auto; padding: 32px clamp(20px, 3vw, 44px) 40px;
        }
        .employee-portal .portal-page-heading { margin-bottom: 28px; }
        .employee-portal .portal-page-heading h1 {
            margin: 0; color: var(--portal-navy); font-size: clamp(1.5rem, 2.4vw, 1.85rem);
            font-weight: 750; letter-spacing: -.035em; line-height: 1.3;
        }
        .employee-portal .portal-page-heading p { margin: 8px 0 0; max-width: 72ch; color: var(--portal-muted); }

        /* Navigation: fixed on desktop, Bootstrap offcanvas on smaller screens. */
        .employee-portal .portal-sidebar {
            --bs-offcanvas-width: min(280px, 88vw);
            background: var(--portal-navy) !important; color: #fff;
        }
        .employee-portal .portal-sidebar-head {
            display: flex; align-items: center; justify-content: space-between;
            flex-shrink: 0; padding: 29px 24px 25px;
        }
        .employee-portal .portal-brand { display: flex; gap: 12px; align-items: center; color: #fff; text-decoration: none; }
        .employee-portal .portal-brand-mark {
            display: grid; place-items: center; width: 42px; height: 42px; flex-shrink: 0;
            border-radius: 12px; background: #2c60d6; font-size: 1.35rem;
        }
        .employee-portal .portal-brand strong { display: block; font-size: 1.3rem; line-height: 1.25; letter-spacing: .04em; }
        .employee-portal .portal-brand small { color: #b6c3d8; font-size: .73rem; }
        .employee-portal .portal-sidebar .offcanvas-body {
            display: flex; flex-direction: column; flex: 1; min-height: 0;
            padding: 12px 16px 20px; overflow-y: auto;
        }
        .employee-portal .portal-nav-label { padding: 0 14px; margin-bottom: 14px; color: #aabbd3; font-size: .66rem; font-weight: 650; letter-spacing: .12em; }
        .employee-portal .portal-nav { display: flex; flex-direction: column; gap: 7px; }
        .employee-portal .portal-nav-link {
            display: flex; gap: 13px; align-items: center; min-height: 48px;
            padding: 11px 14px; border-radius: 10px; text-decoration: none;
            color: #c5d0e1; font-weight: 500; transition: background-color .15s, color .15s;
        }
        .employee-portal .portal-nav-link .bi { font-size: 1.15rem; }
        .employee-portal .portal-nav-link:hover { background: #223754; color: #fff; }
        .employee-portal .portal-nav-link.active { color: #fff; background: #285bd0; box-shadow: inset 3px 0 #8bb9ff; }
        .employee-portal .portal-sidebar-bottom { margin-top: auto; padding: 28px 10px 0; }
        .employee-portal .portal-sidebar-person { display: flex; gap: 11px; align-items: center; border-top: 1px solid #34435a; padding-top: 20px; }
        .employee-portal .portal-sidebar-person small { color: #b6c3d8; }
        .employee-portal .portal-sidebar-person .portal-person-name { color: #fff; font-size: .83rem; }
        .employee-portal .portal-header {
            position: sticky; top: 0; z-index: 1020; display: flex; align-items: center;
            justify-content: space-between; gap: 16px; min-height: 72px;
            padding: 10px clamp(20px, 3vw, 44px); background: #fff; border-bottom: 1px solid var(--portal-line);
        }
        .employee-portal .portal-breadcrumb { margin: 0; display: flex; align-items: center; gap: 10px; padding: 0; list-style: none; font-size: .82rem; }
        .employee-portal .portal-breadcrumb a { color: var(--portal-muted); text-decoration: none; }
        .employee-portal .portal-breadcrumb [aria-current] { color: #26364e; font-weight: 600; }
        .employee-portal .portal-menu-toggle { display: grid; place-items: center; width: 40px; height: 40px; border: 1px solid var(--portal-line); border-radius: 10px; background: #fff; color: var(--portal-navy); font-size: 1.25rem; flex-shrink: 0; }
        .employee-portal .portal-account-button { display: flex; align-items: center; gap: 10px; padding: 2px; border: 0; background: transparent; color: #24334b; text-align: left; border-radius: 9px; }
        .employee-portal .portal-person-name { display: block; font-size: .85rem; font-weight: 650; overflow-wrap: anywhere; }
        .employee-portal .portal-account-meta { max-width: 210px; }
        .employee-portal .portal-account-meta small { display: block; color: var(--portal-muted); font-size: .71rem; }
        .employee-portal .portal-avatar {
            width: 38px; height: 38px; display: grid; place-items: center; flex-shrink: 0;
            border-radius: 50%; background: #e8efff; color: #2455b5; font-size: .8rem; font-weight: 750;
        }
        .employee-portal .portal-avatar-lg { width: 88px; height: 88px; border-radius: 24px; font-size: 1.8rem; background: #e5edff; }
        .employee-portal .portal-account-dropdown { width: 280px; max-width: calc(100vw - 32px); padding: 8px; border: 1px solid var(--portal-line); border-radius: 14px; box-shadow: 0 14px 40px #15243c1a; }
        .employee-portal .portal-account-dropdown .dropdown-item { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 8px; font-size: .88rem; }
        .employee-portal .portal-account-dropdown .dropdown-item:active { background: var(--portal-blue); color: #fff; }
        .employee-portal .portal-footer { display: flex; flex-wrap: wrap; gap: 8px 20px; justify-content: space-between; padding: 18px clamp(20px, 3vw, 44px); border-top: 1px solid var(--portal-line); color: var(--portal-muted); font-size: .75rem; }

        /* Shared cards, data labels, feedback and tables. */
        .employee-portal .portal-card { min-width: 0; background: #fff; border: 1px solid var(--portal-line); border-radius: var(--portal-radius); box-shadow: 0 3px 12px #1c355604; transition: box-shadow .15s; }
        .employee-portal .portal-card:hover { box-shadow: 0 5px 20px #1c355609; }
        .employee-portal .portal-card-header { display: flex; align-items: center; flex-wrap: wrap; gap: 12px; padding: 21px 24px; border-bottom: 1px solid var(--portal-line); }
        .employee-portal .portal-card-header h2 { margin: 0; font-size: 1rem; font-weight: 700; color: var(--portal-navy); }
        .employee-portal .portal-card-body { padding: 24px; }
        .employee-portal .portal-icon { display: grid; place-items: center; width: 38px; height: 38px; flex-shrink: 0; border-radius: 11px; color: #2e60bd; background: #eef3ff; font-size: 1.1rem; }
        .employee-portal .portal-icon-teal { background: #e9f7f4; color: #147163; }
        .employee-portal .portal-badge { display: inline-flex; align-items: center; gap: 6px; width: fit-content; padding: 5px 10px; border-radius: 7px; font-size: .73rem; font-weight: 650; line-height: 1.5; overflow-wrap: anywhere; }
        .employee-portal .portal-badge-success { color: #166547; background: #e9f6ef; }
        .employee-portal .portal-badge-warning { color: #8b580e; background: #fff4d9; }
        .employee-portal .portal-badge-danger { color: #a82e40; background: #fcecef; }
        .employee-portal .portal-badge-info { color: #2855ab; background: #ecf2ff; }
        .employee-portal .portal-badge-neutral { color: #556279; background: #edf0f5; }
        .employee-portal .portal-data-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 24px; margin: 0; }
        .employee-portal .portal-data-grid dt { margin-bottom: 5px; color: var(--portal-muted); font-size: .78rem; font-weight: 400; }
        .employee-portal .portal-data-grid dd { margin: 0; font-weight: 600; overflow-wrap: anywhere; }
        .employee-portal .portal-data-wide { grid-column: 1 / -1; }
        .employee-portal .portal-notice { display: flex; align-items: flex-start; gap: 10px; padding: 14px 16px; border-radius: 10px; color: #476078; background: #edf3fa; font-size: .82rem; }
        .employee-portal .portal-notice .bi { flex-shrink: 0; margin-top: 1px; }
        .employee-portal .portal-empty { padding: 58px 24px; text-align: center; }
        .employee-portal .portal-empty-icon { display: grid; place-items: center; width: 68px; height: 68px; margin: 0 auto 20px; border-radius: 20px; background: #eef3fb; color: #5679b8; font-size: 1.9rem; }
        .employee-portal .portal-empty h2, .employee-portal .portal-empty h3 { margin-bottom: 8px; font-size: 1.08rem; font-weight: 700; }
        .employee-portal .portal-empty p { max-width: 440px; margin: 0 auto; color: var(--portal-muted); font-size: .87rem; }
        .employee-portal .portal-table-scroll { max-width: 100%; border-radius: 0 0 var(--portal-radius) var(--portal-radius); }
        .employee-portal .portal-table { --bs-table-hover-bg: #f7f9fe; margin: 0; min-width: 900px; font-size: .83rem; }
        .employee-portal .portal-table th { padding: 14px 20px; border-bottom-color: var(--portal-line); background: #f8fafc; color: var(--portal-muted); font-size: .71rem; font-weight: 650; white-space: nowrap; }
        .employee-portal .portal-table td { padding: 20px; border-bottom-color: #edf0f5; color: #30415a; vertical-align: middle; }
        .employee-portal .portal-table tbody tr:last-child td { border-bottom: 0; }
        .employee-portal .portal-table-empty { min-width: 0; }
        .employee-portal .portal-table-empty thead { display: none; }
        .employee-portal .portal-table .portal-table-note { max-width: 270px; min-width: 140px; margin-top: 6px; color: var(--portal-muted); font-size: .76rem; overflow-wrap: anywhere; }
        .employee-portal .portal-pagination { margin-top: 22px; }
        .employee-portal .portal-pagination .pagination { margin-bottom: 0; flex-wrap: wrap; gap: 4px; }
        .employee-portal .portal-pagination .page-link { border-radius: 7px; font-size: .84rem; }
        .employee-portal .portal-pagination p { color: var(--portal-muted); font-size: .8rem; }
        .employee-portal .alert { border-radius: 12px; font-size: .88rem; }
        .employee-portal .btn { border-radius: 9px; font-size: .87rem; font-weight: 600; padding: 10px 17px; }
        .employee-portal .btn-primary { --bs-btn-bg: #245bd6; --bs-btn-border-color: #245bd6; --bs-btn-hover-bg: #1d4db8; --bs-btn-hover-border-color: #1d4db8; --bs-btn-disabled-bg: #245bd6; --bs-btn-disabled-border-color: #245bd6; }
        .employee-portal .btn-outline-primary { --bs-btn-color: #245bd6; --bs-btn-border-color: #c6d5f3; --bs-btn-hover-bg: #245bd6; --bs-btn-hover-border-color: #245bd6; }
        .employee-portal .portal-number { font-variant-numeric: tabular-nums; }

        /* Profile and document cards. */
        .employee-portal .portal-profile-overview { padding: 30px; border-top: 3px solid #3a6bd9; background: linear-gradient(110deg, #fff 60%, #f0f5ff); }
        .employee-portal .portal-profile-name { margin-bottom: 8px; font-size: clamp(1.35rem, 2.4vw, 1.8rem); font-weight: 750; color: var(--portal-navy); letter-spacing: -.025em; overflow-wrap: anywhere; }
        .employee-portal .portal-profile-meta { display: flex; flex-wrap: wrap; gap: 8px 20px; color: var(--portal-muted); font-size: .86rem; }
        .employee-portal .portal-profile-meta .bi { margin-right: 5px; color: #5d7aae; }
        .employee-portal .portal-profile-footer { display: flex; flex-wrap: wrap; gap: 12px 28px; margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--portal-line); font-size: .82rem; }
        .employee-portal .portal-profile-footer strong { color: var(--portal-navy); font-weight: 650; }
        .employee-portal .portal-account-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .employee-portal .portal-document-heading { display: flex; flex-wrap: wrap; gap: 18px; align-items: center; justify-content: space-between; }
        .employee-portal .portal-document-number { font-size: .8rem; color: var(--portal-muted); }
        .employee-portal .portal-contract-code { margin: 0; font-size: 1.08rem; font-weight: 700; color: var(--portal-navy); overflow-wrap: anywhere; }
        .employee-portal .portal-contract-dates { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 20px; padding: 22px 24px; }
        .employee-portal .portal-contract-dates small { display: block; margin-bottom: 5px; color: var(--portal-muted); font-size: .75rem; }
        .employee-portal .portal-contract-money { display: grid; grid-template-columns: 1fr .6fr 1fr; gap: 16px; padding: 20px 24px; background: #f9fbfd; border-top: 1px solid var(--portal-line); border-radius: 0 0 var(--portal-radius) var(--portal-radius); }
        .employee-portal .portal-contract-money dt { color: var(--portal-muted); font-size: .73rem; font-weight: 400; margin-bottom: 5px; }
        .employee-portal .portal-contract-money dd { margin: 0; font-size: .87rem; font-weight: 650; overflow-wrap: anywhere; }

        /* Attendance and payslips. */
        .employee-portal .portal-attendance-today { display: grid; grid-template-columns: 1fr auto; gap: 24px; align-items: center; padding: 26px; }
        .employee-portal .portal-today-date { margin: 0 0 7px; font-size: 1.35rem; font-weight: 700; color: var(--portal-navy); }
        .employee-portal .portal-attendance-actions { display: flex; gap: 10px; flex-wrap: wrap; }
        .employee-portal .portal-stat { padding: 20px; height: 100%; }
        .employee-portal .portal-stat-label { margin-bottom: 8px; color: var(--portal-muted); font-size: .78rem; }
        .employee-portal .portal-stat-value { margin-bottom: 3px; color: var(--portal-navy); font-size: 1.45rem; font-weight: 700; line-height: 1.3; overflow-wrap: anywhere; }
        .employee-portal .portal-stat-hint { margin: 0; font-size: .74rem; color: var(--portal-muted); }
        .employee-portal .portal-payslip + .portal-payslip { margin-top: 28px; }
        .employee-portal .portal-payslip-header { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 24px; padding: 26px; border-bottom: 1px solid var(--portal-line); background: #f0f5ff; border-radius: var(--portal-radius) var(--portal-radius) 0 0; }
        .employee-portal .portal-payslip-header > div { min-width: 0; max-width: 100%; overflow-wrap: anywhere; }
        .employee-portal .portal-period-title { margin: 0 0 7px; font-size: 1.2rem; font-weight: 750; color: var(--portal-navy); }
        .employee-portal .portal-net-label { display: block; margin-bottom: 4px; color: #3f5c86; font-size: .76rem; font-weight: 600; }
        .employee-portal .portal-net-amount { color: #1746a0; font-size: clamp(1.35rem, 2.8vw, 1.9rem); font-weight: 750; letter-spacing: -.035em; overflow-wrap: anywhere; }
        .employee-portal .portal-payroll-group { height: 100%; padding: 0 24px; }
        .employee-portal .portal-payroll-group h3 { margin: 0 0 17px; font-size: .73rem; letter-spacing: .045em; text-transform: uppercase; font-weight: 750; color: var(--portal-muted); }
        .employee-portal .portal-payroll-lines { margin: 0; }
        .employee-portal .portal-payroll-lines > div { display: flex; align-items: baseline; justify-content: space-between; gap: 16px; margin-top: 12px; }
        .employee-portal .portal-payroll-lines dt { font-size: .82rem; font-weight: 400; color: var(--portal-muted); }
        .employee-portal .portal-payroll-lines dd { margin: 0; color: var(--portal-navy); font-size: .87rem; font-weight: 650; text-align: right; overflow-wrap: anywhere; }
        .employee-portal .portal-payroll-lines .portal-payroll-total { padding-top: 14px; border-top: 1px solid var(--portal-line); }
        .employee-portal .portal-payroll-total dt, .employee-portal .portal-payroll-total dd { color: #2455b5; font-weight: 700; }
        .employee-portal .portal-overtime { margin: 0 24px 24px; border: 1px solid var(--portal-line); border-radius: 10px; }
        .employee-portal .portal-overtime summary { padding: 14px 16px; cursor: pointer; font-size: .84rem; font-weight: 600; }
        .employee-portal .portal-overtime[open] summary { border-bottom: 1px solid var(--portal-line); }
        .employee-portal .portal-overtime .portal-table { min-width: 650px; }
        .employee-portal .portal-payslip-note { margin: 0 24px 24px; font-size: .82rem; color: var(--portal-muted); overflow-wrap: anywhere; }

        @media (min-width: 992px) {
            .employee-portal .portal-sidebar.offcanvas-lg { position: fixed; inset: 0 auto 0 0; display: flex; flex-direction: column; width: 252px; height: 100vh; height: 100dvh; z-index: 1030; }
            .employee-portal .portal-workspace { margin-left: 252px; }
        }
        @media (max-width: 1199.98px) {
            .employee-portal .portal-attendance-today { grid-template-columns: 1fr; }
            .employee-portal .portal-account-meta { max-width: 155px; }
        }
        @media (max-width: 767.98px) {
            .employee-portal .portal-main { padding: 24px 16px 32px; }
            .employee-portal .portal-header { padding: 12px 16px; min-height: 66px; gap: 10px; }
            .employee-portal .portal-page-heading { margin-bottom: 22px; }
            .employee-portal .portal-breadcrumb { font-size: .76rem; gap: 6px; }
            .employee-portal .portal-footer { padding: 18px 16px; }
            .employee-portal .portal-profile-overview, .employee-portal .portal-card-body { padding: 22px; }
            .employee-portal .portal-profile-overview { background: #fff; }
            .employee-portal .portal-card-header { padding: 18px 20px; }
            .employee-portal .portal-account-grid { grid-template-columns: 1fr; gap: 20px; }
            .employee-portal .portal-attendance-today, .employee-portal .portal-payslip-header { padding: 22px; }
            .employee-portal .portal-net { width: 100%; padding-top: 18px; border-top: 1px solid #d8e3f6; }
            .employee-portal .portal-contract-money { grid-template-columns: 1fr 1fr; }
            .employee-portal .portal-contract-money > div:first-child { grid-column: 1 / -1; }
        }
        @media (max-width: 479.98px) {
            .employee-portal .portal-data-grid { grid-template-columns: 1fr; gap: 20px; }
            .employee-portal .portal-avatar-lg { width: 68px; height: 68px; border-radius: 18px; font-size: 1.4rem; }
            .employee-portal .portal-attendance-actions { flex-direction: column; }
            .employee-portal .portal-attendance-actions .btn { width: 100%; }
            .employee-portal .portal-contract-dates { gap: 16px; padding-inline: 20px; }
            .employee-portal .portal-stat { padding: 16px; }
            .employee-portal .portal-stat-value { font-size: 1.18rem; }
            .employee-portal .portal-payroll-lines > div { flex-wrap: wrap; gap: 3px 12px; }
        }
        @media (prefers-reduced-motion: reduce) {
            .employee-portal *, .employee-portal *::before, .employee-portal *::after { transition: none !important; scroll-behavior: auto !important; }
        }
    </style>
    @stack('styles')
</head>
<body class="employee-portal">
    @php
        $portalUser = auth()->user();
        // Use data already present in the view or loaded on the authenticated user.
        $portalEmployee = $employee ?? ($portalUser->relationLoaded('employee') ? $portalUser->getRelation('employee') : null);
        $portalName = $portalEmployee?->full_name ?: $portalUser->email;
        $portalNameParts = preg_split('/\s+/u', trim($portalName), -1, PREG_SPLIT_NO_EMPTY);
        $portalInitials = mb_strtoupper(mb_substr($portalNameParts[0] ?? 'HR', 0, 1).(count($portalNameParts) > 1 ? mb_substr(end($portalNameParts), 0, 1) : ''));
        $portalRole = $portalUser->relationLoaded('role') ? $portalUser->getRelation('role')?->name : null;
    @endphp
    <a class="portal-skip-link" href="#main-content">Chuyển đến nội dung chính</a>
    @include('user.partial.sidebar')
    <div class="portal-workspace">
        @include('user.partial.header')
        <main id="main-content" class="portal-main" tabindex="-1">
            <div class="portal-page-heading">
                <div class="portal-eyebrow">Không gian nhân viên</div>
                <h1>@yield('title', 'Hồ sơ của tôi')</h1>
                @hasSection('description')<p>@yield('description')</p>@endif
            </div>

            @foreach(['success' => 'check-circle', 'error' => 'exclamation-circle', 'warning' => 'exclamation-triangle', 'info' => 'info-circle', 'status' => 'info-circle'] as $flashType => $flashIcon)
                @if(session()->has($flashType))
                    <div class="alert alert-{{ $flashType === 'error' ? 'danger' : ($flashType === 'status' ? 'info' : $flashType) }} d-flex gap-2 align-items-start" role="{{ in_array($flashType, ['error', 'warning']) ? 'alert' : 'status' }}">
                        <i class="bi bi-{{ $flashIcon }}" aria-hidden="true"></i>
                        <div>{{ session($flashType) }}</div>
                    </div>
                @endif
            @endforeach
            @if($errors->any())
                <div class="alert alert-danger" role="alert">
                    <div class="fw-semibold mb-1"><i class="bi bi-exclamation-circle me-2" aria-hidden="true"></i>Vui lòng kiểm tra lại thông tin</div>
                    <ul class="mb-0 ps-4">
                        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
        @include('user.partial.footer')
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
