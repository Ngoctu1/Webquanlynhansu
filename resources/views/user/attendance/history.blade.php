@extends('user.layout.masterlayout')
@section('title', 'Lịch sử chấm công')
@section('description', 'Tra cứu thời gian làm việc và kết quả chấm công của bạn')

@section('content')
@php
    $attendanceStatuses = [
        'checked_in' => ['Đã check-in', 'info'],
        'completed' => ['Đã hoàn tất', 'success'],
        'adjusted' => ['Đã điều chỉnh', 'warning'],
    ];
@endphp

<section class="portal-card mb-4" aria-labelledby="attendanceFilterHeading">
    <div class="portal-card-header justify-content-between">
        <div class="d-flex align-items-center gap-3">
            <span class="portal-icon"><i class="bi bi-funnel" aria-hidden="true"></i></span>
            <h2 id="attendanceFilterHeading">Bộ lọc lịch sử</h2>
        </div>
        <a class="btn btn-outline-primary btn-sm" href="{{ route('user.attendances') }}">
            <i class="bi bi-arrow-left me-2" aria-hidden="true"></i>Về trang chấm công
        </a>
    </div>
    <form class="attendance-history-filters" method="GET" action="{{ route('user.attendances.history') }}" aria-describedby="attendanceFilterHelp">
        <div class="row g-3">
            <div class="col-12 col-md-6 col-xl-3">
                <label class="form-label" for="historyFrom">Từ ngày</label>
                <input class="form-control @error('from') is-invalid @enderror" type="date" id="historyFrom" name="from" value="{{ old('from', $filters['from'] ?? '') }}"
                    @error('from') aria-invalid="true" aria-describedby="historyFromError" @enderror>
                @error('from')
                    <div class="invalid-feedback" id="historyFromError">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <label class="form-label" for="historyTo">Đến ngày</label>
                <input class="form-control @error('to') is-invalid @enderror" type="date" id="historyTo" name="to" value="{{ old('to', $filters['to'] ?? '') }}"
                    @error('to') aria-invalid="true" aria-describedby="historyToError" @enderror>
                @error('to')
                    <div class="invalid-feedback" id="historyToError">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-12 col-md-6 col-xl-2">
                <label class="form-label" for="historyMonth">Tháng</label>
                <select class="form-select @error('month') is-invalid @enderror" id="historyMonth" name="month"
                    @error('month') aria-invalid="true" aria-describedby="historyMonthError" @enderror>
                    <option value="">Tất cả tháng</option>
                    @foreach(range(1, 12) as $month)
                        <option value="{{ $month }}" @selected((string) old('month', $filters['month'] ?? '') === (string) $month)>Tháng {{ $month }}</option>
                    @endforeach
                </select>
                @error('month')
                    <div class="invalid-feedback" id="historyMonthError">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-12 col-md-6 col-xl-2">
                <label class="form-label" for="historyYear">Năm</label>
                <input class="form-control @error('year') is-invalid @enderror" type="number" id="historyYear" name="year" min="1" max="9999" step="1"
                    value="{{ old('year', $filters['year'] ?? '') }}" placeholder="{{ $attendanceNow->format('Y') }}"
                    @error('year') aria-invalid="true" aria-describedby="historyYearError" @enderror>
                @error('year')
                    <div class="invalid-feedback" id="historyYearError">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-12 col-md-6 col-xl-2">
                <label class="form-label" for="historyStatus">Trạng thái</label>
                <select class="form-select @error('status') is-invalid @enderror" id="historyStatus" name="status"
                    @error('status') aria-invalid="true" aria-describedby="historyStatusError" @enderror>
                    <option value="">Tất cả trạng thái</option>
                    @foreach($attendanceStatuses as $status => $statusLabel)
                        <option value="{{ $status }}" @selected(old('status', $filters['status'] ?? '') === $status)>{{ $statusLabel[0] }}</option>
                    @endforeach
                </select>
                @error('status')
                    <div class="invalid-feedback" id="historyStatusError">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mt-4">
            <p class="small portal-muted mb-0" id="attendanceFilterHelp">Chọn năm khi lọc theo tháng. Các bộ lọc được áp dụng đồng thời.</p>
            <div class="d-flex flex-wrap gap-2">
                <button class="btn btn-primary" type="submit"><i class="bi bi-search me-2" aria-hidden="true"></i>Lọc</button>
                <a class="btn btn-outline-secondary" href="{{ route('user.attendances.history') }}"><i class="bi bi-arrow-counterclockwise me-2" aria-hidden="true"></i>Đặt lại</a>
            </div>
        </div>
    </form>
</section>

<section class="portal-card" aria-labelledby="attendanceHistoryHeading">
    <div class="portal-card-header justify-content-between">
        <div class="d-flex align-items-center gap-3">
            <span class="portal-icon portal-icon-teal"><i class="bi bi-clock-history" aria-hidden="true"></i></span>
            <h2 id="attendanceHistoryHeading">Lịch sử của bạn</h2>
        </div>
        <span class="small portal-muted">{{ $attendances->total() }} bản ghi · Mới nhất trước</span>
    </div>
    <div class="table-responsive portal-table-scroll" role="region" aria-label="Bảng lịch sử chấm công, cuộn ngang để xem đầy đủ" tabindex="0">
        <table class="table table-hover portal-table {{ $attendances->count() ? '' : 'portal-table-empty' }}">
            <caption class="visually-hidden">Lịch sử chấm công cá nhân theo bộ lọc, mỗi trang tối đa 10 bản ghi.</caption>
            <thead>
                <tr>
                    <th scope="col">STT</th>
                    <th scope="col">Ngày</th>
                    <th scope="col">Giờ vào</th>
                    <th scope="col">Giờ ra</th>
                    <th scope="col">Số công</th>
                    <th scope="col">Đi muộn</th>
                    <th scope="col">Tăng ca</th>
                    <th scope="col">Trạng thái</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attendances as $row)
                    @php
                        $rowBadge = $attendanceStatuses[$row->status] ?? [$row->status ?: 'Chưa cập nhật', 'neutral'];
                        $rowLateMinutes = $row->getAttributes()['late_minutes'] ?? null;
                        $rowIncomplete = $row->status === 'checked_in' && $row->check_in && !$row->check_out;
                        $rowMissingCheckout = $rowIncomplete && $row->work_date->format('Y-m-d') < $attendanceNow->format('Y-m-d');
                    @endphp
                    <tr>
                        <td class="portal-number">{{ $attendances->firstItem() + $loop->index }}</td>
                        <td class="text-nowrap fw-semibold portal-number">{{ $row->work_date->format('d/m/Y') }}</td>
                        <td class="text-nowrap portal-number">{{ $row->check_in?->format('H:i:s') ?? 'Chưa ghi nhận' }}</td>
                        <td class="text-nowrap portal-number">
                            {{ $row->check_out?->format('d/m H:i:s') ?? 'Chưa ghi nhận' }}
                            @if($row->check_in && $row->check_out)
                                <div class="portal-table-note">{{ number_format($row->check_in->diffInMinutes($row->check_out), 0) }} phút thực tế</div>
                            @endif
                        </td>
                        <td class="portal-number">
                            @if($rowIncomplete)
                                <span class="portal-badge portal-badge-warning">{{ $rowMissingCheckout ? 'Chờ HR xử lý' : 'Chờ hoàn tất' }}</span>
                            @else
                                <span class="fw-semibold">{{ $row->work_units }} công</span>
                            @endif
                        </td>
                        <td>
                            @if($rowLateMinutes !== null)
                                <span class="portal-badge portal-badge-{{ $rowLateMinutes > 0 ? 'warning' : 'neutral' }} portal-number">{{ $rowLateMinutes }} phút</span>
                            @else
                                <span class="portal-muted">Chưa cập nhật</span>
                            @endif
                        </td>
                        <td class="portal-number">
                            @if($rowIncomplete)
                                <span class="portal-muted">Chưa xác nhận</span>
                            @else
                                {{ $row->overtime_hours }} giờ
                            @endif
                        </td>
                        <td>
                            <span class="portal-badge portal-badge-{{ $rowBadge[1] }}">{{ $rowBadge[0] }}</span>
                            @if($rowMissingCheckout)
                                <div class="portal-table-note text-danger">Chưa check-out. Vui lòng liên hệ HR.</div>
                            @endif
                            @if($row->adjustment_reason)
                                <div class="portal-table-note">{{ $row->adjustment_reason }}</div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <div class="portal-empty">
                                <span class="portal-empty-icon"><i class="bi bi-calendar2-week" aria-hidden="true"></i></span>
                                <h3>Không tìm thấy dữ liệu chấm công</h3>
                                <p>Chưa có bản ghi phù hợp. Bạn có thể thay đổi bộ lọc hoặc đặt lại để xem toàn bộ lịch sử của mình.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
<div class="portal-pagination">{{ $attendances->links('pagination::bootstrap-5') }}</div>
@endsection

@push('styles')
<style>
    .employee-portal .attendance-history-filters { padding: 24px; }
    .employee-portal .attendance-history-filters .form-label { font-size: .82rem; font-weight: 600; color: var(--portal-navy); }
    .employee-portal .attendance-history-filters .form-control,
    .employee-portal .attendance-history-filters .form-select { min-height: 42px; min-width: 0; max-width: 100%; border-radius: 8px; font-size: .86rem; }
    .employee-portal .attendance-history-filters .btn { min-height: 42px; }
    @media (max-width: 575.98px) {
        .employee-portal .attendance-history-filters { padding: 18px; }
    }
</style>
@endpush
