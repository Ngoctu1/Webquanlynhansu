@extends('user.layout.masterlayout')
@section('title', 'Đăng ký nghỉ phép')

@section('content')
@php
    $leaveTypeMap = [
        'annual'   => ['Nghỉ phép năm', 'bi-calendar-heart', 'badge bg-primary-subtle text-primary border border-primary-subtle'],
        'sick'     => ['Nghỉ ốm đau', 'bi-heart-pulse', 'badge bg-danger-subtle text-danger border border-danger-subtle'],
        'personal' => ['Việc riêng', 'bi-house-heart', 'badge bg-warning-subtle text-warning-emphasis border border-warning-subtle'],
        'unpaid'   => ['Nghỉ không lương', 'bi-cash-coin', 'badge bg-secondary-subtle text-secondary border border-secondary-subtle'],
    ];

    $pendingCount = $leaveRequests->where('status', 'pending')->count();
    $approvedCount = $leaveRequests->where('status', 'approved')->count();
@endphp

{{-- Header --}}
<div class="portal-page-heading d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <p class="portal-eyebrow mb-1">Chế độ &amp; Quyền lợi</p>
        <h1 class="h3 fw-bold mb-1">Quản lý nghỉ phép</h1>
        <p class="text-muted mb-0">Tạo đơn xin nghỉ phép trực tuyến và theo dõi kết quả xét duyệt từ HR.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-white text-primary border shadow-sm px-3 py-2 rounded-pill fs-6">
            <i class="bi bi-clock-history me-1 text-warning"></i>{{ $pendingCount }} đơn đang chờ
        </span>
        <span class="badge bg-white text-success border shadow-sm px-3 py-2 rounded-pill fs-6">
            <i class="bi bi-check2-circle me-1 text-success"></i>{{ $approvedCount }} đơn đã duyệt
        </span>
    </div>
</div>

<div class="row g-4 mb-4">
    {{-- FORM TẠO ĐƠN NGHỈ PHÉP --}}
    <div class="col-12 col-xl-5">
        <div class="portal-card h-100">
            <div class="portal-card-header">
                <span class="portal-icon"><i class="bi bi-send-plus-fill"></i></span>
                <div>
                    <h2>Tạo đơn nghỉ mới</h2>
                    <p>Điền thời gian và lý do gửi đến phòng Nhân sự.</p>
                </div>
            </div>
            <div class="portal-card-body">
                <form method="POST" action="{{ route('user.leave-requests.store') }}">
                    @csrf

                    {{-- Khoảng thời gian --}}
                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
                            <label class="form-label" for="start_date">
                                <i class="bi bi-calendar-event me-1 text-primary"></i>Từ ngày
                            </label>
                            <input id="start_date"
                                   name="start_date"
                                   type="date"
                                   class="form-control @error('start_date') is-invalid @enderror"
                                   value="{{ old('start_date') }}"
                                   min="{{ today()->toDateString() }}"
                                   required>
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label" for="end_date">
                                <i class="bi bi-calendar-check me-1 text-primary"></i>Đến ngày
                            </label>
                            <input id="end_date"
                                   name="end_date"
                                   type="date"
                                   class="form-control @error('end_date') is-invalid @enderror"
                                   value="{{ old('end_date') }}"
                                   min="{{ today()->toDateString() }}"
                                   required>
                            @error('end_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Loại nghỉ --}}
                    <div class="mb-3">
                        <label class="form-label" for="leave_type">
                            <i class="bi bi-tag me-1 text-primary"></i>Loại nghỉ phép
                        </label>
                        <select id="leave_type" name="leave_type" class="form-select @error('leave_type') is-invalid @enderror" required>
                            @foreach(['annual'=>'Nghỉ phép năm (hưởng lương)','sick'=>'Nghỉ ốm đau','personal'=>'Việc riêng có lương/không lương','unpaid'=>'Nghỉ không hưởng lương'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('leave_type') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('leave_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Lý do --}}
                    <div class="mb-4">
                        <label class="form-label" for="reason">
                            <i class="bi bi-chat-left-text me-1 text-primary"></i>Lý do xin nghỉ
                        </label>
                        <textarea id="reason"
                                  name="reason"
                                  class="form-control @error('reason') is-invalid @enderror"
                                  rows="4"
                                  maxlength="5000"
                                  placeholder="Ghi rõ lý do xin nghỉ và người bàn giao công việc (nếu có)..."
                                  required>{{ old('reason') }}</textarea>
                        @error('reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text mt-1 text-muted small">
                            <i class="bi bi-info-circle me-1"></i>Không thể gửi đơn có thời gian trùng với đơn đang chờ hoặc đã duyệt.
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-send-fill"></i> Gửi đơn nghỉ phép
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- BẢNG DANH SÁCH ĐƠN NGHỈ PHÉP --}}
    <div class="col-12 col-xl-7">
        <div class="portal-card h-100 d-flex flex-column">
            <div class="portal-card-header justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <span class="portal-icon portal-icon-teal"><i class="bi bi-clock-history"></i></span>
                    <div>
                        <h2>Lịch sử gửi đơn</h2>
                        <p>Danh sách các đơn nghỉ phép bạn đã gửi.</p>
                    </div>
                </div>
                <span class="portal-document-number">{{ $leaveRequests->total() }} đơn</span>
            </div>

            <div class="table-responsive flex-grow-1">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Thời gian</th>
                            <th>Loại nghỉ</th>
                            <th>Lý do</th>
                            <th>Trạng thái</th>
                            <th>Phản hồi</th>
                            <th class="text-end">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($leaveRequests as $leaveRequest)
                            @php
                                $typeInfo = $leaveTypeMap[$leaveRequest->leave_type] ?? ['Khác', 'bi-calendar', 'badge bg-light text-dark'];
                                $daysDiff = $leaveRequest->start_date->diffInDays($leaveRequest->end_date) + 1;
                            @endphp
                            <tr>
                                {{-- Ngày nghỉ --}}
                                <td>
                                    <div class="fw-bold text-nowrap">{{ $leaveRequest->start_date->format('d/m/Y') }}</div>
                                    <div class="small text-muted text-nowrap">đến {{ $leaveRequest->end_date->format('d/m/Y') }}</div>
                                    <span class="badge bg-light text-primary border mt-1" style="font-size:10.5px;">{{ $daysDiff }} ngày</span>
                                </td>

                                {{-- Loại nghỉ --}}
                                <td>
                                    <span class="{{ $typeInfo[2] }}" style="font-size:11.5px;">
                                        <i class="bi {{ $typeInfo[1] }} me-1"></i>{{ $typeInfo[0] }}
                                    </span>
                                </td>

                                {{-- Lý do --}}
                                <td>
                                    <div class="text-break" style="max-width: 220px; font-size: 13px;">
                                        {{ $leaveRequest->reason }}
                                    </div>
                                </td>

                                {{-- Trạng thái --}}
                                <td>
                                    @if($leaveRequest->status === 'pending')
                                        <span class="portal-status-pill portal-status-pending">
                                            <i class="bi bi-hourglass-split"></i>Chờ duyệt
                                        </span>
                                    @elseif($leaveRequest->status === 'approved')
                                        <span class="portal-status-pill portal-status-approved">
                                            <i class="bi bi-check2-circle"></i>Đã duyệt
                                        </span>
                                    @elseif($leaveRequest->status === 'rejected')
                                        <span class="portal-status-pill portal-status-rejected">
                                            <i class="bi bi-x-circle"></i>Bị từ chối
                                        </span>
                                    @elseif($leaveRequest->status === 'cancelled')
                                        <span class="portal-status-pill portal-status-cancelled">
                                            <i class="bi bi-dash-circle"></i>Đã hủy
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">{{ $leaveRequest->status }}</span>
                                    @endif
                                </td>

                                {{-- Phản hồi HR --}}
                                <td>
                                    @if($leaveRequest->rejection_reason)
                                        <span class="small text-danger d-block" title="{{ $leaveRequest->rejection_reason }}">
                                            <i class="bi bi-chat-square-quote me-1"></i>{{ Str::limit($leaveRequest->rejection_reason, 25) }}
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>

                                {{-- Thao tác --}}
                                <td class="text-end text-nowrap">
                                    @if($leaveRequest->status === 'pending')
                                        <form method="POST"
                                              action="{{ route('user.leave-requests.cancel', $leaveRequest) }}"
                                              onsubmit="return confirm('Bạn có chắc chắn muốn hủy đơn xin nghỉ phép này không?');"
                                              class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2" title="Hủy đơn">
                                                <i class="bi bi-trash3 me-1"></i>Hủy
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="empty-state">
                                        <i class="bi bi-calendar-check text-muted mb-2" style="font-size:32px;"></i>
                                        <h5 class="text-muted">Chưa có đơn nghỉ phép nào</h5>
                                        <p class="small text-muted">Các đơn xin nghỉ bạn tạo sẽ được lưu lại lịch sử tại bảng này.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-3 border-top mt-auto d-flex justify-content-center">
                {{ $leaveRequests->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection
