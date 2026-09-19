{{-- ================================================================
     DASHBOARD (Welcome Page)
     resources/views/admin/welcome.blade.php
     ================================================================ --}}
@extends('admin.layout.masterlayout')

@section('title', 'Dashboard')

{{-- ===== Page-specific Styles ===== --}}
@section('styles')
<style>
    /* ====================================================
       DASHBOARD STYLES
       ==================================================== */

    /* ---- Page Title ---- */
    .page-title-row {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 12px;
    }

    .page-title {
        font-size: 1.6rem;
        font-weight: 700;
        color: var(--text-primary);
        letter-spacing: -0.03em;
        margin: 0;
    }

    .page-subtitle {
        color: var(--text-secondary);
        font-size: 0.875rem;
        margin: 2px 0 0 0;
    }

    .page-date {
        display: flex;
        align-items: center;
        gap: 6px;
        color: var(--text-secondary);
        font-size: 0.82rem;
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        padding: 8px 14px;
        border-radius: 10px;
    }

    /* ---- Stat Cards ---- */
    .stat-card {
        background: var(--card-bg);
        border-radius: var(--radius);
        padding: 22px;
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        gap: 18px;
        transition: transform .2s, box-shadow .2s;
        height: 100%;
    }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-md);
    }

    .stat-icon-wrap {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
    }

    .stat-icon-blue   { background: #dbeafe; color: #2563eb; }
    .stat-icon-green  { background: #d1fae5; color: #059669; }
    .stat-icon-purple { background: #ede9fe; color: #7c3aed; }
    .stat-icon-amber  { background: #fef3c7; color: #d97706; }

    .stat-info { flex: 1; min-width: 0; }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: var(--text-primary);
        line-height: 1.1;
        letter-spacing: -0.03em;
    }

    .stat-label {
        font-size: 0.82rem;
        color: var(--text-secondary);
        margin-top: 3px;
    }

    .stat-trend {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 2px 8px;
        border-radius: 20px;
        margin-top: 6px;
    }

    .trend-up   { background: #d1fae5; color: #059669; }
    .trend-down { background: #fee2e2; color: #dc2626; }

    /* ---- Section Card ---- */
    .section-card {
        background: var(--card-bg);
        border-radius: var(--radius);
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--border-color);
        overflow: hidden;
        height: 100%;
    }

    .section-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 18px 22px;
        border-bottom: 1px solid var(--border-color);
        background: #fafbfc;
    }

    .section-card-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0;
    }

    .section-card-title i {
        color: var(--primary-color);
        font-size: 1rem;
    }

    .section-card-body { padding: 20px 22px; }

    .btn-see-all {
        font-size: 0.78rem;
        font-weight: 600;
        color: var(--primary-color);
        text-decoration: none;
        padding: 5px 12px;
        border-radius: 8px;
        transition: background .2s;
    }

    .btn-see-all:hover { background: var(--primary-light); }

    /* ---- Charts ---- */
    .chart-container { position: relative; width: 100%; }

    /* ---- Tables ---- */
    .hrm-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 0.85rem;
    }

    .hrm-table thead th {
        background: #f8fafc;
        color: var(--text-secondary);
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .05em;
        padding: 10px 14px;
        border-bottom: 1px solid var(--border-color);
        white-space: nowrap;
    }

    .hrm-table tbody tr {
        transition: background .15s;
    }

    .hrm-table tbody tr:hover td { background: #f8faff; }

    .hrm-table tbody td {
        padding: 12px 14px;
        border-bottom: 1px solid #f1f5f9;
        color: var(--text-primary);
        vertical-align: middle;
    }

    .hrm-table tbody tr:last-child td { border-bottom: none; }

    /* Employee avatar in table */
    .emp-avatar {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: 700;
        color: #fff;
        flex-shrink: 0;
    }

    .emp-name { font-weight: 600; color: var(--text-primary); font-size: 0.875rem; }
    .emp-id   { font-size: 0.75rem; color: var(--text-secondary); }

    /* Status badges */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 0.73rem;
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 20px;
        white-space: nowrap;
    }

    .status-badge::before {
        content: '';
        width: 6px;
        height: 6px;
        border-radius: 50%;
    }

    .status-active    { background: #d1fae5; color: #065f46; }
    .status-active::before    { background: #059669; }
    .status-leave     { background: #fef3c7; color: #92400e; }
    .status-leave::before     { background: #d97706; }
    .status-pending   { background: #e0e7ff; color: #3730a3; }
    .status-pending::before   { background: #6366f1; }
    .status-approved  { background: #d1fae5; color: #065f46; }
    .status-approved::before  { background: #059669; }

    /* ---- Activity Feed ---- */
    .activity-item {
        display: flex;
        gap: 14px;
        align-items: flex-start;
        padding: 10px 0;
        border-bottom: 1px solid #f1f5f9;
    }

    .activity-item:last-child { border-bottom: none; }

    .activity-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.95rem;
        flex-shrink: 0;
    }

    .activity-body { flex: 1; }
    .activity-text { font-size: 0.83rem; color: var(--text-primary); line-height: 1.45; }
    .activity-time { font-size: 0.73rem; color: var(--text-secondary); margin-top: 2px; }

    /* ---- Quick Stats ---- */
    .quick-stat {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid #f1f5f9;
    }

    .quick-stat:last-child { border-bottom: none; }

    .quick-stat-label {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 0.85rem;
        color: var(--text-primary);
    }

    .quick-stat-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
    }

    .quick-stat-value {
        font-size: 0.85rem;
        font-weight: 700;
        color: var(--text-primary);
    }

    .quick-stat-bar {
        height: 5px;
        border-radius: 3px;
        background: #f1f5f9;
        margin-top: 5px;
        overflow: hidden;
    }

    .quick-stat-bar-fill {
        height: 100%;
        border-radius: 3px;
        transition: width 1s ease;
    }

    /* ---- Welcome Banner ---- */
    .welcome-banner {
        background: linear-gradient(135deg, #1e40af 0%, #7c3aed 60%, #0ea5e9 100%);
        border-radius: var(--radius);
        padding: 28px 32px;
        color: #fff;
        position: relative;
        overflow: hidden;
        margin-bottom: 24px;
    }

    .welcome-banner::before {
        content: '';
        position: absolute;
        top: -50px;
        right: -50px;
        width: 200px;
        height: 200px;
        background: rgba(255,255,255,.08);
        border-radius: 50%;
    }

    .welcome-banner::after {
        content: '';
        position: absolute;
        bottom: -60px;
        right: 80px;
        width: 150px;
        height: 150px;
        background: rgba(255,255,255,.05);
        border-radius: 50%;
    }

    .welcome-title {
        font-size: 1.35rem;
        font-weight: 700;
        margin-bottom: 4px;
    }

    .welcome-sub {
        font-size: 0.875rem;
        opacity: 0.8;
        margin-bottom: 16px;
    }

    .welcome-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        position: relative;
        z-index: 1;
    }

    .btn-welcome {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 18px;
        border-radius: 10px;
        font-size: 0.85rem;
        font-weight: 600;
        text-decoration: none;
        transition: all .2s;
    }

    .btn-welcome-primary {
        background: #fff;
        color: #1e40af;
    }

    .btn-welcome-primary:hover {
        background: #f0f9ff;
        color: #1e40af;
    }

    .btn-welcome-outline {
        background: rgba(255,255,255,.15);
        color: #fff;
        border: 1px solid rgba(255,255,255,.3);
    }

    .btn-welcome-outline:hover {
        background: rgba(255,255,255,.25);
        color: #fff;
    }

    /* ---- Responsive ---- */
    @media (max-width: 767.98px) {
        .page-title { font-size: 1.3rem; }
        .stat-value  { font-size: 1.6rem; }
    }
</style>
@endsection

{{-- ===== Page Content ===== --}}
@section('content')

{{-- ====================================================
     WELCOME BANNER
     ==================================================== --}}
<div class="welcome-banner">
    <div style="position:relative;z-index:1;">
        <h2 class="welcome-title">Xin chào, Admin HRM! 👋</h2>
        <p class="welcome-sub">Hôm nay là Thứ Bảy, 13 tháng 9 năm 2026 &mdash; Chúc bạn một ngày làm việc hiệu quả!</p>
        <div class="welcome-actions">
            <a href="#" class="btn-welcome btn-welcome-primary">
                <i class="bi bi-person-plus-fill"></i> Thêm nhân viên
            </a>
            <a href="#" class="btn-welcome btn-welcome-outline">
                <i class="bi bi-bar-chart-line"></i> Xem báo cáo
            </a>
        </div>
    </div>
    <i class="bi bi-people-fill" style="position:absolute;right:32px;top:50%;transform:translateY(-50%);font-size:6rem;opacity:.1;"></i>
</div>

{{-- ====================================================
     STAT CARDS
     ==================================================== --}}
<div class="row g-4 mb-4">

    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon-wrap stat-icon-blue">
                <i class="bi bi-people-fill"></i>
            </div>
            <div class="stat-info">
                <div class="stat-value">248</div>
                <div class="stat-label">Tổng số nhân viên</div>
                <div class="stat-trend trend-up">
                    <i class="bi bi-arrow-up"></i> +12 tháng này
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon-wrap stat-icon-green">
                <i class="bi bi-diagram-3-fill"></i>
            </div>
            <div class="stat-info">
                <div class="stat-value">8</div>
                <div class="stat-label">Phòng ban</div>
                <div class="stat-trend trend-up">
                    <i class="bi bi-arrow-up"></i> +1 mới
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon-wrap stat-icon-purple">
                <i class="bi bi-person-check-fill"></i>
            </div>
            <div class="stat-info">
                <div class="stat-value">231</div>
                <div class="stat-label">Đang làm việc</div>
                <div class="stat-trend trend-up">
                    <i class="bi bi-arrow-up"></i> 93.1% tỷ lệ
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon-wrap stat-icon-amber">
                <i class="bi bi-calendar-x-fill"></i>
            </div>
            <div class="stat-info">
                <div class="stat-value">17</div>
                <div class="stat-label">Đang nghỉ phép</div>
                <div class="stat-trend trend-down">
                    <i class="bi bi-arrow-down"></i> -3 so với tuần trước
                </div>
            </div>
        </div>
    </div>

</div>{{-- /row stat cards --}}

{{-- ====================================================
     CHARTS ROW
     ==================================================== --}}
<div class="row g-4 mb-4">

    {{-- Chart: Nhân sự theo phòng ban --}}
    <div class="col-12 col-lg-7">
        <div class="section-card">
            <div class="section-card-header">
                <h3 class="section-card-title">
                    <i class="bi bi-bar-chart-fill"></i> Nhân sự theo phòng ban
                </h3>
                <div class="d-flex align-items-center gap-2">
                    <select class="form-select form-select-sm" style="width:auto;border-radius:8px;" id="chart-dept-filter">
                        <option>Tháng 9/2026</option>
                        <option>Tháng 8/2026</option>
                        <option>Quý 3/2026</option>
                    </select>
                </div>
            </div>
            <div class="section-card-body">
                <div class="chart-container" style="height:280px;">
                    <canvas id="chartDepartment"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Chart: Chấm công trong tuần --}}
    <div class="col-12 col-lg-5">
        <div class="section-card">
            <div class="section-card-header">
                <h3 class="section-card-title">
                    <i class="bi bi-clock-history"></i> Chấm công tuần này
                </h3>
                <a href="#" class="btn-see-all">Xem chi tiết</a>
            </div>
            <div class="section-card-body">
                <div class="chart-container" style="height:220px;">
                    <canvas id="chartAttendance"></canvas>
                </div>
                {{-- Legend --}}
                <div class="d-flex justify-content-center gap-4 mt-3">
                    <div class="d-flex align-items-center gap-2 small">
                        <span style="width:12px;height:12px;border-radius:50%;background:#2563eb;display:inline-block;"></span>
                        Đúng giờ
                    </div>
                    <div class="d-flex align-items-center gap-2 small">
                        <span style="width:12px;height:12px;border-radius:50%;background:#f59e0b;display:inline-block;"></span>
                        Đi muộn
                    </div>
                    <div class="d-flex align-items-center gap-2 small">
                        <span style="width:12px;height:12px;border-radius:50%;background:#ef4444;display:inline-block;"></span>
                        Vắng mặt
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>{{-- /chart row --}}

{{-- ====================================================
     DATA TABLES ROW
     ==================================================== --}}
<div class="row g-4 mb-4">

    {{-- Table: Nhân viên mới --}}
    <div class="col-12 col-xl-7">
        <div class="section-card">
            <div class="section-card-header">
                <h3 class="section-card-title">
                    <i class="bi bi-person-plus-fill"></i> Nhân viên mới nhất
                </h3>
                <a href="#" class="btn-see-all">Xem tất cả <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="table-responsive">
                <table class="hrm-table">
                    <thead>
                        <tr>
                            <th>Nhân viên</th>
                            <th>Phòng ban</th>
                            <th>Chức vụ</th>
                            <th>Ngày vào</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                        $newEmployees = [
                            ['name'=>'Nguyễn Văn An',    'id'=>'NV-2460', 'dept'=>'Kỹ thuật IT',    'pos'=>'Lập trình viên',  'date'=>'10/09/2026', 'status'=>'active',  'color'=>'#2563eb', 'initials'=>'NA'],
                            ['name'=>'Trần Thị Bích',    'id'=>'NV-2459', 'dept'=>'Kinh doanh',      'pos'=>'Nhân viên KD',    'date'=>'08/09/2026', 'status'=>'active',  'color'=>'#7c3aed', 'initials'=>'TB'],
                            ['name'=>'Lê Minh Cường',    'id'=>'NV-2458', 'dept'=>'Nhân sự',         'pos'=>'Chuyên viên NS',  'date'=>'05/09/2026', 'status'=>'active',  'color'=>'#059669', 'initials'=>'LC'],
                            ['name'=>'Phạm Thu Dung',    'id'=>'NV-2457', 'dept'=>'Kế toán',         'pos'=>'Kế toán viên',   'date'=>'03/09/2026', 'status'=>'leave',   'color'=>'#d97706', 'initials'=>'PD'],
                            ['name'=>'Hoàng Văn Đức',   'id'=>'NV-2456', 'dept'=>'Marketing',       'pos'=>'Content Creator', 'date'=>'01/09/2026', 'status'=>'active',  'color'=>'#0ea5e9', 'initials'=>'HD'],
                            ['name'=>'Vũ Thị Lan',       'id'=>'NV-2455', 'dept'=>'Hành chính',      'pos'=>'Thư ký',          'date'=>'28/08/2026', 'status'=>'active',  'color'=>'#ec4899', 'initials'=>'VL'],
                        ];
                        @endphp

                        @foreach($newEmployees as $emp)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="emp-avatar" style="background:{{ $emp['color'] }};">
                                        {{ $emp['initials'] }}
                                    </div>
                                    <div>
                                        <div class="emp-name">{{ $emp['name'] }}</div>
                                        <div class="emp-id">{{ $emp['id'] }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $emp['dept'] }}</td>
                            <td>{{ $emp['pos'] }}</td>
                            <td>{{ $emp['date'] }}</td>
                            <td>
                                @if($emp['status'] === 'active')
                                    <span class="status-badge status-active">Đang làm</span>
                                @elseif($emp['status'] === 'leave')
                                    <span class="status-badge status-leave">Nghỉ phép</span>
                                @else
                                    <span class="status-badge status-pending">Thử việc</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Table: Nhân viên nghỉ phép & Activity --}}
    <div class="col-12 col-xl-5 d-flex flex-column gap-4">

        {{-- Leave Table --}}
        <div class="section-card">
            <div class="section-card-header">
                <h3 class="section-card-title">
                    <i class="bi bi-calendar-x-fill"></i> Nghỉ phép gần đây
                </h3>
                <a href="#" class="btn-see-all">Xem tất cả</a>
            </div>
            <div class="table-responsive">
                <table class="hrm-table">
                    <thead>
                        <tr>
                            <th>Nhân viên</th>
                            <th>Loại nghỉ</th>
                            <th>Ngày</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                        $leaveRequests = [
                            ['name'=>'Phạm Thu Dung',  'initials'=>'PD', 'color'=>'#d97706', 'type'=>'Nghỉ phép năm',  'date'=>'09-12/09', 'status'=>'approved'],
                            ['name'=>'Đỗ Quang Huy',   'initials'=>'DH', 'color'=>'#7c3aed', 'type'=>'Nghỉ ốm',         'date'=>'11/09',     'status'=>'approved'],
                            ['name'=>'Bùi Thị Mai',    'initials'=>'BM', 'color'=>'#ec4899', 'type'=>'Nghỉ cá nhân',    'date'=>'13/09',     'status'=>'pending'],
                            ['name'=>'Ngô Văn Nam',    'initials'=>'NN', 'color'=>'#0ea5e9', 'type'=>'Nghỉ phép năm',  'date'=>'14-16/09',  'status'=>'pending'],
                        ];
                        @endphp

                        @foreach($leaveRequests as $leave)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="emp-avatar" style="background:{{ $leave['color'] }};width:28px;height:28px;font-size:.7rem;border-radius:8px;">
                                        {{ $leave['initials'] }}
                                    </div>
                                    <span style="font-size:.83rem;font-weight:600;">{{ $leave['name'] }}</span>
                                </div>
                            </td>
                            <td style="font-size:.8rem;">{{ $leave['type'] }}</td>
                            <td style="font-size:.78rem;color:var(--text-secondary);">{{ $leave['date'] }}</td>
                            <td>
                                @if($leave['status'] === 'approved')
                                    <span class="status-badge status-approved">Đã duyệt</span>
                                @else
                                    <span class="status-badge status-pending">Chờ duyệt</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Activity Feed --}}
        <div class="section-card">
            <div class="section-card-header">
                <h3 class="section-card-title">
                    <i class="bi bi-activity"></i> Hoạt động gần đây
                </h3>
            </div>
            <div class="section-card-body" style="padding:16px 22px;">
                <div class="activity-item">
                    <div class="activity-icon bg-primary-soft text-primary">
                        <i class="bi bi-person-plus"></i>
                    </div>
                    <div class="activity-body">
                        <p class="activity-text mb-0">Thêm nhân viên <strong>Nguyễn Văn An</strong> vào hệ thống.</p>
                        <span class="activity-time"><i class="bi bi-clock"></i> 10 phút trước</span>
                    </div>
                </div>
                <div class="activity-item">
                    <div class="activity-icon bg-warning-soft text-warning">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <div class="activity-body">
                        <p class="activity-text mb-0">Duyệt đơn nghỉ phép của <strong>Đỗ Quang Huy</strong>.</p>
                        <span class="activity-time"><i class="bi bi-clock"></i> 35 phút trước</span>
                    </div>
                </div>
                <div class="activity-item">
                    <div class="activity-icon bg-success-soft text-success">
                        <i class="bi bi-cash-coin"></i>
                    </div>
                    <div class="activity-body">
                        <p class="activity-text mb-0">Xuất bảng lương tháng 8 thành công.</p>
                        <span class="activity-time"><i class="bi bi-clock"></i> 2 giờ trước</span>
                    </div>
                </div>
                <div class="activity-item">
                    <div class="activity-icon bg-danger-soft text-danger">
                        <i class="bi bi-file-earmark-text"></i>
                    </div>
                    <div class="activity-body">
                        <p class="activity-text mb-0">Hợp đồng <strong>NV-2430</strong> sắp hết hạn trong 5 ngày.</p>
                        <span class="activity-time"><i class="bi bi-clock"></i> 5 giờ trước</span>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>{{-- /tables row --}}

{{-- ====================================================
     QUICK STATS ROW
     ==================================================== --}}
<div class="row g-4 mb-4">

    {{-- Phân bổ nhân sự theo phòng ban --}}
    <div class="col-12 col-md-6 col-xl-4">
        <div class="section-card">
            <div class="section-card-header">
                <h3 class="section-card-title">
                    <i class="bi bi-pie-chart-fill"></i> Phân bổ nhân sự
                </h3>
            </div>
            <div class="section-card-body">
                @php
                $deptStats = [
                    ['name'=>'Kỹ thuật IT',    'count'=>52, 'pct'=>21, 'color'=>'#2563eb'],
                    ['name'=>'Kinh doanh',      'count'=>47, 'pct'=>19, 'color'=>'#7c3aed'],
                    ['name'=>'Nhân sự',         'count'=>18, 'pct'=>7,  'color'=>'#059669'],
                    ['name'=>'Kế toán',         'count'=>24, 'pct'=>10, 'color'=>'#d97706'],
                    ['name'=>'Marketing',       'count'=>31, 'pct'=>13, 'color'=>'#0ea5e9'],
                    ['name'=>'Hành chính',      'count'=>21, 'pct'=>8,  'color'=>'#ec4899'],
                    ['name'=>'Vận hành',        'count'=>35, 'pct'=>14, 'color'=>'#6366f1'],
                    ['name'=>'Khác',            'count'=>20, 'pct'=>8,  'color'=>'#94a3b8'],
                ];
                @endphp

                @foreach($deptStats as $dept)
                <div class="quick-stat">
                    <div class="quick-stat-label">
                        <span class="quick-stat-dot" style="background:{{ $dept['color'] }};"></span>
                        {{ $dept['name'] }}
                    </div>
                    <div class="quick-stat-value">{{ $dept['count'] }} NV</div>
                </div>
                <div class="quick-stat-bar">
                    <div class="quick-stat-bar-fill" style="width:{{ $dept['pct'] }}%;background:{{ $dept['color'] }};"></div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Tổng hợp hợp đồng --}}
    <div class="col-12 col-md-6 col-xl-4">
        <div class="section-card">
            <div class="section-card-header">
                <h3 class="section-card-title">
                    <i class="bi bi-file-earmark-check-fill"></i> Tình trạng hợp đồng
                </h3>
            </div>
            <div class="section-card-body">
                <div class="chart-container" style="height:230px;">
                    <canvas id="chartContracts"></canvas>
                </div>
                <div class="row g-2 mt-2">
                    <div class="col-6">
                        <div class="d-flex align-items-center gap-2 p-2 rounded-3" style="background:#f0fdf4;">
                            <i class="bi bi-check-circle-fill text-success"></i>
                            <div>
                                <div style="font-size:.72rem;color:#64748b;">Còn hiệu lực</div>
                                <div style="font-size:1.1rem;font-weight:700;color:#065f46;">216</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center gap-2 p-2 rounded-3" style="background:#fef9ec;">
                            <i class="bi bi-exclamation-circle-fill text-warning"></i>
                            <div>
                                <div style="font-size:.72rem;color:#64748b;">Sắp hết hạn</div>
                                <div style="font-size:1.1rem;font-weight:700;color:#92400e;">18</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center gap-2 p-2 rounded-3" style="background:#fff5f5;">
                            <i class="bi bi-x-circle-fill text-danger"></i>
                            <div>
                                <div style="font-size:.72rem;color:#64748b;">Đã hết hạn</div>
                                <div style="font-size:1.1rem;font-weight:700;color:#991b1b;">8</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center gap-2 p-2 rounded-3" style="background:#f0f4ff;">
                            <i class="bi bi-clock-fill text-primary"></i>
                            <div>
                                <div style="font-size:.72rem;color:#64748b;">Thử việc</div>
                                <div style="font-size:1.1rem;font-weight:700;color:#1e40af;">6</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Đánh giá & Lương --}}
    <div class="col-12 col-xl-4">
        <div class="section-card h-100">
            <div class="section-card-header">
                <h3 class="section-card-title">
                    <i class="bi bi-star-fill"></i> Top nhân viên xuất sắc
                </h3>
                <span class="badge bg-primary-soft text-primary" style="font-size:.72rem;">Tháng 9</span>
            </div>
            <div class="section-card-body">
                @php
                $topEmployees = [
                    ['name'=>'Nguyễn Hoàng Nam',  'dept'=>'Kỹ thuật IT',  'score'=>98, 'color'=>'#2563eb', 'initials'=>'NH', 'rank'=>1],
                    ['name'=>'Trần Thị Mỹ Linh',  'dept'=>'Kinh doanh',    'score'=>96, 'color'=>'#7c3aed', 'initials'=>'TL', 'rank'=>2],
                    ['name'=>'Lê Văn Phúc',        'dept'=>'Marketing',     'score'=>94, 'color'=>'#059669', 'initials'=>'LP', 'rank'=>3],
                    ['name'=>'Phạm Thanh Hương',   'dept'=>'Nhân sự',       'score'=>92, 'color'=>'#d97706', 'initials'=>'PH', 'rank'=>4],
                    ['name'=>'Vũ Minh Tuấn',       'dept'=>'Vận hành',      'score'=>90, 'color'=>'#0ea5e9', 'initials'=>'VT', 'rank'=>5],
                ];
                @endphp

                @foreach($topEmployees as $top)
                <div class="d-flex align-items-center gap-3 py-2" style="border-bottom:1px solid #f1f5f9;">
                    <div style="font-size:.8rem;font-weight:700;color:
                        @if($top['rank']===1) #f59e0b
                        @elseif($top['rank']===2) #94a3b8
                        @elseif($top['rank']===3) #d97706
                        @else var(--text-secondary) @endif;
                        width:20px;text-align:center;">
                        @if($top['rank'] <= 3)
                            <i class="bi bi-trophy-fill"></i>
                        @else
                            {{ $top['rank'] }}
                        @endif
                    </div>
                    <div class="emp-avatar" style="background:{{ $top['color'] }};width:34px;height:34px;font-size:.75rem;border-radius:10px;">
                        {{ $top['initials'] }}
                    </div>
                    <div class="flex-1">
                        <div class="emp-name" style="font-size:.83rem;">{{ $top['name'] }}</div>
                        <div class="emp-id">{{ $top['dept'] }}</div>
                    </div>
                    <div>
                        <span class="badge" style="background:{{ $top['color'] }}15;color:{{ $top['color'] }};font-size:.75rem;font-weight:700;border-radius:8px;padding:4px 10px;">
                            {{ $top['score'] }}%
                        </span>
                    </div>
                </div>
                @endforeach

                <div class="text-center mt-3">
                    <a href="#" class="btn btn-sm" style="background:#f0f4ff;color:#2563eb;border-radius:8px;font-size:.8rem;font-weight:600;padding:6px 20px;border:none;">
                        Xem bảng xếp hạng đầy đủ
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>{{-- /quick stats row --}}

@endsection

{{-- ===== Page Scripts ===== --}}
@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ============================================================
       CHART 1: Nhân sự theo phòng ban (Horizontal Bar)
       ============================================================ */
    const ctxDept = document.getElementById('chartDepartment');
    if (ctxDept) {
        new Chart(ctxDept, {
            type: 'bar',
            data: {
                labels: ['Kỹ thuật IT', 'Kinh doanh', 'Marketing', 'Vận hành', 'Kế toán', 'Nhân sự', 'Hành chính'],
                datasets: [{
                    label: 'Số nhân viên',
                    data: [52, 47, 31, 35, 24, 18, 21],
                    backgroundColor: [
                        'rgba(37,99,235,.85)',
                        'rgba(124,58,237,.85)',
                        'rgba(14,165,233,.85)',
                        'rgba(99,102,241,.85)',
                        'rgba(217,119,6,.85)',
                        'rgba(5,150,105,.85)',
                        'rgba(236,72,153,.85)'
                    ],
                    borderRadius: 8,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                return ' ' + ctx.raw + ' nhân viên';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { color: '#f1f5f9', drawBorder: false },
                        ticks: { font: { size: 11 }, color: '#94a3b8' }
                    },
                    y: {
                        grid: { display: false },
                        ticks: { font: { size: 11, weight: '500' }, color: '#475569' }
                    }
                }
            }
        });
    }

    /* ============================================================
       CHART 2: Chấm công trong tuần (Stacked Bar)
       ============================================================ */
    const ctxAttend = document.getElementById('chartAttendance');
    if (ctxAttend) {
        new Chart(ctxAttend, {
            type: 'bar',
            data: {
                labels: ['Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7'],
                datasets: [
                    {
                        label: 'Đúng giờ',
                        data: [220, 215, 225, 218, 212, 185],
                        backgroundColor: 'rgba(37,99,235,.85)',
                        borderRadius: 4,
                        borderSkipped: false,
                    },
                    {
                        label: 'Đi muộn',
                        data: [8, 12, 5, 9, 14, 7],
                        backgroundColor: 'rgba(245,158,11,.85)',
                        borderRadius: 4,
                        borderSkipped: false,
                    },
                    {
                        label: 'Vắng mặt',
                        data: [3, 4, 1, 4, 5, 9],
                        backgroundColor: 'rgba(239,68,68,.85)',
                        borderRadius: 4,
                        borderSkipped: false,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { mode: 'index', intersect: false }
                },
                scales: {
                    x: {
                        stacked: true,
                        grid: { display: false },
                        ticks: { font: { size: 11 }, color: '#94a3b8' }
                    },
                    y: {
                        stacked: true,
                        grid: { color: '#f1f5f9', drawBorder: false },
                        ticks: { font: { size: 11 }, color: '#94a3b8' }
                    }
                }
            }
        });
    }

    /* ============================================================
       CHART 3: Tình trạng hợp đồng (Doughnut)
       ============================================================ */
    const ctxContract = document.getElementById('chartContracts');
    if (ctxContract) {
        new Chart(ctxContract, {
            type: 'doughnut',
            data: {
                labels: ['Còn hiệu lực', 'Sắp hết hạn', 'Đã hết hạn', 'Thử việc'],
                datasets: [{
                    data: [216, 18, 8, 6],
                    backgroundColor: [
                        '#059669',
                        '#f59e0b',
                        '#ef4444',
                        '#2563eb'
                    ],
                    borderWidth: 3,
                    borderColor: '#fff',
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                return ' ' + ctx.label + ': ' + ctx.raw + ' hợp đồng';
                            }
                        }
                    }
                }
            }
        });
    }

    /* ============================================================
       Animate progress bars
       ============================================================ */
    setTimeout(function () {
        document.querySelectorAll('.quick-stat-bar-fill').forEach(function (bar) {
            const w = bar.style.width;
            bar.style.width = '0';
            setTimeout(function () { bar.style.width = w; }, 50);
        });
    }, 200);

    /* ============================================================
       Update date display
       ============================================================ */
    const dateEl = document.getElementById('current-date');
    if (dateEl) {
        const now = new Date();
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        dateEl.textContent = now.toLocaleDateString('vi-VN', options);
    }

});
</script>
@endsection
