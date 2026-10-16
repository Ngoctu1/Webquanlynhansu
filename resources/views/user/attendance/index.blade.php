@extends('user.layout.masterlayout')
@section('title', 'Chấm công của tôi')
@section('description', 'Theo dõi và ghi nhận thời gian làm việc hằng ngày')

@section('content')
@php
    $todayCheckedIn = (bool) $todayAttendance?->check_in;
    $todayCheckedOut = (bool) $todayAttendance?->check_out;
    $attendanceStatuses = [
        'checked_in' => ['Đã check-in', 'info'],
        'completed' => ['Đã hoàn tất', 'success'],
        'adjusted' => ['Đã điều chỉnh', 'warning'],
    ];
    $todayBadge = $todayAttendance
        ? ($attendanceStatuses[$todayAttendance->status] ?? [$todayAttendance->status ?: 'Chưa cập nhật', 'neutral'])
        : ['Chưa check-in', 'neutral'];
    $clockReference = now('Asia/Ho_Chi_Minh');
    $clockWeekdays = ['Chủ Nhật', 'Thứ Hai', 'Thứ Ba', 'Thứ Tư', 'Thứ Năm', 'Thứ Sáu', 'Thứ Bảy'];
@endphp

<section class="portal-card attendance-clock mb-4" id="attendanceClock"
    data-server-time="{{ $clockReference->getTimestamp() * 1000 + (int) $clockReference->format('v') }}"
    data-work-date="{{ $attendanceNow->format('Y-m-d') }}"
    aria-labelledby="attendanceClockHeading">
    <div class="attendance-clock-heading">
        <span class="attendance-clock-icon"><i class="bi bi-clock" aria-hidden="true"></i></span>
        <h2 id="attendanceClockHeading">Thời gian hiện tại</h2>
    </div>
    <div role="timer" aria-live="off" aria-labelledby="attendanceClockHeading">
        <time class="attendance-clock-time" id="attendanceClockTime" datetime="{{ $clockReference->toIso8601String() }}">{{ $clockReference->format('H:i:s') }}</time>
        <p class="attendance-clock-date" id="attendanceClockDate">{{ $clockWeekdays[(int) $clockReference->format('w')] }}, ngày {{ $clockReference->format('d') }} tháng {{ $clockReference->format('m') }} năm {{ $clockReference->format('Y') }}</p>
    </div>
    <span class="attendance-clock-zone"><i class="bi bi-globe-asia-australia" aria-hidden="true"></i>Múi giờ Việt Nam (GMT+7)</span>
    <p class="attendance-clock-note">Tham chiếu giờ máy chủ. Thời điểm chấm công được ghi nhận khi bạn thực hiện thao tác.</p>
    <noscript><p class="small portal-muted mb-0">Đồng hồ đang hiển thị thời điểm tải trang.</p></noscript>
</section>

<div class="alert alert-info" id="attendanceDayChanged" role="status" hidden>
    <i class="bi bi-calendar-event me-2" aria-hidden="true"></i>Đã sang ngày mới. Thông tin chấm công bên dưới thuộc ngày {{ $attendanceNow->format('d/m/Y') }}.
    <a class="alert-link" href="{{ route('user.attendances') }}">Mở chấm công hôm nay</a> để cập nhật trạng thái và thao tác.
</div>

<section class="portal-card attendance-day-summary mb-3" aria-labelledby="attendanceToday">
    <div>
        <div class="portal-eyebrow mb-1">Thông tin chấm công</div>
        <h2 id="attendanceToday">Ngày {{ $attendanceNow->format('d/m/Y') }}</h2>
        <p>Check-in từ 30 phút trước giờ bắt đầu ca theo lịch làm việc của bạn.</p>
    </div>
    <span class="portal-badge portal-badge-{{ $todayBadge[1] }}">{{ $todayBadge[0] }}</span>
</section>



<section class="portal-card attendance-action-panel mb-4" aria-labelledby="attendanceActionHeading">
    <div>
        <h2 id="attendanceActionHeading">Ghi nhận thời gian làm việc</h2>
        <p>Với ca trong ngày, hãy check-out trước 00:00 của ngày tiếp theo.</p>
    </div>
    <div class="portal-attendance-actions">
        <form method="POST" action="{{ route('user.attendances.check-in') }}">
            @csrf
            <button type="submit" class="btn btn-primary" @if($todayCheckedIn || $todayCheckedOut) disabled @endif>
                <i class="bi bi-box-arrow-in-right me-2" aria-hidden="true"></i>{{ $todayCheckedIn ? 'Đã check-in' : ($todayCheckedOut ? 'Đã hoàn tất hôm nay' : 'Check-in hôm nay') }}
            </button>
        </form>
        <form method="POST" action="{{ route('user.attendances.check-out') }}">
            @csrf
            <button type="submit" class="btn btn-outline-primary" @if(!$todayCheckedIn || $todayCheckedOut) disabled @endif>
                <i class="bi bi-box-arrow-right me-2" aria-hidden="true"></i>{{ $todayCheckedOut ? 'Đã check-out' : ($todayCheckedIn ? 'Check-out hôm nay' : 'Cần check-in trước') }}
            </button>
        </form>
    </div>
</section>
<div class="row g-3 mb-3">
    <div class="col-12 col-sm-6">
        <section class="portal-card attendance-punch-card h-100" aria-labelledby="attendanceCheckInHeading">
            <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                <span class="portal-icon portal-icon-teal"><i class="bi bi-box-arrow-in-right" aria-hidden="true"></i></span>
                <span class="portal-badge portal-badge-{{ $todayCheckedIn ? 'success' : 'neutral' }}">{{ $todayCheckedIn ? 'Đã check-in' : 'Chưa check-in' }}</span>
            </div>
            <h3 id="attendanceCheckInHeading">Giờ check-in thực tế</h3>
            <div class="attendance-punch-time portal-number">{{ $todayAttendance?->check_in?->format('H:i:s') ?? ($todayAttendance ? 'Chưa ghi nhận' : 'Chưa có thông tin') }}</div>
            <div class="attendance-punch-detail">
                <span>Số công hôm nay</span>
                <strong class="portal-number">{{ $todayAttendance && $todayAttendance->status === 'checked_in' ? 'Chờ hoàn tất' : ($todayAttendance?->work_units ?? 'Chưa có thông tin') }}</strong>
            </div>
        </section>
    </div>
    <div class="col-12 col-sm-6">
        <section class="portal-card attendance-punch-card h-100" aria-labelledby="attendanceCheckOutHeading">
            <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                <span class="portal-icon"><i class="bi bi-box-arrow-right" aria-hidden="true"></i></span>
                <span class="portal-badge portal-badge-{{ $todayCheckedOut ? 'success' : 'neutral' }}">{{ $todayCheckedOut ? 'Đã check-out' : 'Chưa check-out' }}</span>
            </div>
            <h3 id="attendanceCheckOutHeading">Giờ check-out thực tế</h3>
            <div class="attendance-punch-time portal-number">{{ $todayAttendance?->check_out?->format('H:i:s') ?? ($todayAttendance ? 'Chưa ghi nhận' : 'Chưa có thông tin') }}</div>
            <div class="attendance-punch-detail">
                <span>Tăng ca (giờ)</span>
                <strong class="portal-number">{{ $todayAttendance && $todayAttendance->status === 'checked_in' ? 'Chưa xác nhận' : ($todayAttendance?->overtime_hours ?? 'Chưa có thông tin') }}</strong>
            </div>
        </section>
    </div>
</div>
@if($missingCheckouts->isNotEmpty())
    <div class="alert alert-warning" role="alert">
        <div class="fw-semibold mb-2"><i class="bi bi-exclamation-triangle me-2" aria-hidden="true"></i>Chấm công cần được xử lý</div>
        <ul class="mb-2 ps-4">
            @foreach($missingCheckouts as $missingCheckout)
                <li>Bạn chưa check-out ngày {{ $missingCheckout->work_date->format('d/m/Y') }}. Vui lòng liên hệ HR.</li>
            @endforeach
        </ul>
        <div class="small">Bạn vẫn có thể check-in ngày làm việc mới trong khung giờ cho phép. Bản ghi cũ được giữ nguyên để HR xử lý.</div>
    </div>
@endif

<div class="portal-notice mb-4">
    <i class="bi bi-info-circle" aria-hidden="true"></i>
    <div>Công và phút muộn được tính theo lịch làm việc; tăng ca cần HR xác nhận. Nếu quên check-out, vui lòng liên hệ HR để được xử lý.</div>
</div>

@endsection


@push('styles')
<style>
    .employee-portal .attendance-clock {
        padding: 28px 24px 24px;
        text-align: center;
        background: linear-gradient(135deg, #f2f7ff, #fff 65%, #eff8f6);
        border-color: #dce6f5;
    }
    .employee-portal .attendance-clock-heading {
        display: flex; align-items: center; justify-content: center; gap: 10px;
        margin-bottom: 12px; color: #47658e;
    }
    .employee-portal .attendance-clock-heading h2 {
        margin: 0; font-size: .73rem; font-weight: 750;
        letter-spacing: .1em; text-transform: uppercase;
    }
    .employee-portal .attendance-clock-icon {
        display: grid; place-items: center; width: 32px; height: 32px;
        border-radius: 10px; background: #e5eefe; color: #245bd6; font-size: 1rem;
    }
    .employee-portal .attendance-clock-time {
        display: block; margin: 0 0 10px; color: #17365f;
        font-family: ui-monospace, "Cascadia Mono", "Segoe UI Mono", Consolas, monospace;
        font-variant-numeric: tabular-nums; font-size: clamp(2.5rem, 7vw, 5rem);
        font-weight: 700; line-height: 1.15; letter-spacing: -.04em; white-space: nowrap;
    }
    .employee-portal .attendance-clock-date { margin: 0 0 14px; font-size: 1rem; font-weight: 600; color: #3c536f; }
    .employee-portal .attendance-clock-zone {
        display: inline-flex; align-items: center; gap: 7px; padding: 5px 11px;
        border-radius: 20px; background: #e7f4f0; color: #226655; font-size: .75rem; font-weight: 600;
    }
    .employee-portal .attendance-clock-note { margin: 14px auto 0; max-width: 65ch; color: var(--portal-muted); font-size: .75rem; }
    .employee-portal .attendance-day-summary {
        display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap;
        gap: 14px; padding: 18px 24px;
    }
    .employee-portal .attendance-day-summary h2 { margin: 0; font-size: 1rem; font-weight: 700; color: var(--portal-navy); }
    .employee-portal .attendance-day-summary p { margin: 4px 0 0; font-size: .8rem; color: var(--portal-muted); }
    .employee-portal .attendance-punch-card { padding: 22px 24px; }
    .employee-portal .attendance-punch-card h3 { margin: 0 0 6px; font-size: .8rem; font-weight: 500; color: var(--portal-muted); }
    .employee-portal .attendance-punch-time { min-height: 38px; font-size: clamp(1.15rem, 2.5vw, 1.75rem); font-weight: 700; color: var(--portal-navy); overflow-wrap: anywhere; }
    .employee-portal .attendance-punch-detail {
        display: flex; align-items: baseline; justify-content: space-between; flex-wrap: wrap;
        gap: 6px 14px; margin-top: 18px; padding-top: 14px; border-top: 1px solid var(--portal-line);
        font-size: .76rem; color: var(--portal-muted);
    }
    .employee-portal .attendance-punch-detail strong { font-weight: 600; color: #40516b; }
    .employee-portal .attendance-action-panel { display: flex; flex-wrap: wrap; gap: 20px; justify-content: space-between; align-items: center; padding: 22px 24px; }
    .employee-portal .attendance-action-panel h2 { margin: 0 0 5px; font-size: .95rem; font-weight: 700; }
    .employee-portal .attendance-action-panel p { margin: 0; font-size: .8rem; color: var(--portal-muted); }
    .employee-portal .attendance-action-panel .btn { min-height: 46px; }
    .employee-portal .attendance-action-panel .btn:disabled { opacity: 1; background: #edf1f6; border-color: #dce3ed; color: #627086; }
    @media (max-width: 575.98px) {
        .employee-portal .attendance-clock { padding: 24px 16px 20px; }
        .employee-portal .attendance-clock-date { font-size: .88rem; }
        .employee-portal .attendance-day-summary, .employee-portal .attendance-punch-card, .employee-portal .attendance-action-panel { padding: 18px; }
        .employee-portal .attendance-action-panel .portal-attendance-actions { width: 100%; flex-direction: column; }
        .employee-portal .attendance-action-panel .btn { width: 100%; }
    }
</style>
@endpush

@push('scripts')
<script>
(() => {
    'use strict';

    const clock = document.getElementById('attendanceClock');
    const timeDisplay = document.getElementById('attendanceClockTime');
    const dateDisplay = document.getElementById('attendanceClockDate');
    const dayChanged = document.getElementById('attendanceDayChanged');
    if (!clock || !timeDisplay || !dateDisplay) return;

    const serverEpoch = Number(clock.dataset.serverTime);
    if (!Number.isFinite(serverEpoch)) return;

    // Anchor to receipt of the server response, including time spent loading the page's scripts.
    // The clock is display-only; it never supplies timestamps to attendance forms.
    const navigation = performance.getEntriesByType('navigation')[0];
    const referenceTick = navigation && navigation.responseStart > 0
        ? navigation.responseStart
        : performance.now();
    const timeZone = 'Asia/Ho_Chi_Minh';
    const timeFormatter = new Intl.DateTimeFormat('vi-VN', {
        timeZone, numberingSystem: 'latn', hourCycle: 'h23',
        hour: '2-digit', minute: '2-digit', second: '2-digit'
    });
    const dateFormatter = new Intl.DateTimeFormat('vi-VN', {
        timeZone, numberingSystem: 'latn', weekday: 'long',
        day: '2-digit', month: '2-digit', year: 'numeric'
    });
    let timer;

    function renderClock() {
        clearTimeout(timer);
        const timestamp = serverEpoch + Math.max(0, performance.now() - referenceTick);
        const current = new Date(timestamp);
        timeDisplay.textContent = timeFormatter.format(current);
        timeDisplay.setAttribute('datetime', current.toISOString());

        const parts = {};
        for (const part of dateFormatter.formatToParts(current)) parts[part.type] = part.value;
        dateDisplay.textContent = parts.weekday + ', ngày ' + parts.day + ' tháng ' + parts.month + ' năm ' + parts.year;
        if (dayChanged) {
            dayChanged.hidden = parts.year + '-' + parts.month + '-' + parts.day === clock.dataset.workDate;
        }

        // Recompute from elapsed time, so delayed/background ticks do not accumulate drift.
        if (!document.hidden) timer = setTimeout(renderClock, 1000 - (timestamp % 1000));
    }

    renderClock();
    document.addEventListener('visibilitychange', renderClock);
    window.addEventListener('pageshow', renderClock);
    window.addEventListener('pagehide', () => clearTimeout(timer));
})();
</script>
@endpush

