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

    $entitled = $balance['entitled'] ?? ($employee->annual_leave_days ?? 12);
    $used = $balance['used'] ?? 0;
    $remaining = $balance['remaining'] ?? max(0, $entitled - $used);
@endphp

{{-- Header --}}
<div class="portal-page-heading d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <p class="portal-eyebrow mb-1">Chế độ &amp; Quyền lợi</p>
        <h1 class="h3 fw-bold mb-1">Quản lý nghỉ phép cá nhân</h1>
        <p class="text-muted mb-0">Theo dõi quỹ phép năm {{ now()->year }} và gửi đơn xin nghỉ phép trực tuyến.</p>
    </div>
</div>

{{-- 3 CON SỐ QUỸ PHÉP NĂM: ĐƯỢC HƯỞNG - ĐÃ DÙNG - CÒN LẠI --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="portal-card p-3 border-start border-4 border-primary">
            <div class="text-muted small fw-semibold text-uppercase">Số ngày phép được hưởng ({{ now()->year }})</div>
            <div class="h3 fw-bold text-primary mb-0 mt-1">{{ rtrim(rtrim(number_format($entitled, 2), '0'), '.') }} <span class="fs-6 fw-normal text-muted">ngày</span></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="portal-card p-3 border-start border-4 border-warning">
            <div class="text-muted small fw-semibold text-uppercase">Số ngày phép năm đã dùng</div>
            <div class="h3 fw-bold text-warning-emphasis mb-0 mt-1">{{ rtrim(rtrim(number_format($used, 2), '0'), '.') }} <span class="fs-6 fw-normal text-muted">ngày</span></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="portal-card p-3 border-start border-4 border-success">
            <div class="text-muted small fw-semibold text-uppercase">Số ngày phép năm còn lại</div>
            <div class="h3 fw-bold text-success mb-0 mt-1">{{ rtrim(rtrim(number_format($remaining, 2), '0'), '.') }} <span class="fs-6 fw-normal text-muted">ngày</span></div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    {{-- FORM TẠO ĐƠN NGHỈ PHÉP --}}
    <div class="col-12 col-xl-5">
        <div class="portal-card h-100">
            <div class="portal-card-header">
                <span class="portal-icon"><i class="bi bi-send-plus-fill"></i></span>
                <div>
                    <h2>{{ auth()->user()->isDirector() ? 'Ghi nhận lịch nghỉ (Giám đốc)' : 'Tạo đơn nghỉ phép mới' }}</h2>
                    <p>Hệ thống tự động loại trừ Thứ Bảy và Chủ Nhật khi tính số ngày nghỉ.</p>
                </div>
            </div>
            <div class="portal-card-body">
                @if(auth()->user()->isDirector())
                    <div class="alert alert-info py-2 small mb-3">
                        <i class="bi bi-shield-check me-1"></i>
                        <strong>Đặc quyền Giám đốc:</strong> Lịch nghỉ của Giám đốc được áp dụng trực tiếp và ghi nhận vào Nhật ký hệ thống (không qua luồng chờ duyệt).
                    </div>
                @else
                    <div class="alert alert-light border py-2 small mb-3">
                        <div class="fw-semibold mb-1"><i class="bi bi-info-circle text-primary me-1"></i>Quy tắc đăng ký nghỉ phép:</div>
                        <ul class="mb-0 ps-3">
                            <li>Chỉ gửi đơn vào ngày làm việc (Thứ Hai &ndash; Thứ Sáu).</li>
                            <li>Nghỉ phép năm, việc riêng, không lương phải <strong>báo trước ít nhất 7 ngày</strong> (Ngoại lệ: Nghỉ ốm).</li>
                            <li>Tối đa <strong>2 đơn / tháng</strong>; tối đa <strong>2 người / phòng ban / ngày</strong> đối với phép năm.</li>
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('user.leave-requests.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label" for="leave_type">
                            <i class="bi bi-tag me-1 text-primary"></i>Loại nghỉ phép
                        </label>
                        <select id="leave_type" name="leave_type" class="form-select @error('leave_type') is-invalid @enderror" required>
                            @foreach([
                                'annual' => 'Nghỉ phép năm (hưởng lương - báo trước >= 7 ngày)',
                                'sick' => 'Nghỉ ốm đau (không cần báo trước 7 ngày, có thể đính kèm giấy xác nhận)',
                                'personal' => 'Việc riêng (báo trước >= 7 ngày)',
                                'unpaid' => 'Nghỉ không hưởng lương (báo trước >= 7 ngày)'
                            ] as $value => $label)
                                <option value="{{ $value }}" @selected(old('leave_type') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('leave_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
                            <label class="form-label" for="start_date">
                                <i class="bi bi-calendar-event me-1 text-primary"></i>Từ ngày
                            </label>
                            <input id="start_date" name="start_date" type="date"
                                   class="form-control @error('start_date') is-invalid @enderror"
                                   value="{{ old('start_date') }}" required>
                            @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label" for="end_date">
                                <i class="bi bi-calendar-check me-1 text-primary"></i>Đến ngày
                            </label>
                            <input id="end_date" name="end_date" type="date"
                                   class="form-control @error('end_date') is-invalid @enderror"
                                   value="{{ old('end_date') }}" required>
                            @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="attachment">
                            <i class="bi bi-paperclip me-1 text-primary"></i>File giấy xác nhận (Dành cho Nghỉ ốm / Minh chứng)
                        </label>
                        <input id="attachment" name="attachment" type="file"
                               accept=".pdf,.jpg,.jpeg,.png"
                               class="form-control @error('attachment') is-invalid @enderror">
                        <div class="form-text small">Hỗ trợ PDF, JPG, PNG (tối đa 5MB). Lưu bảo mật tại <code>storage/app/private/leaves/</code>.</div>
                        @error('attachment')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="reason">
                            <i class="bi bi-chat-left-text me-1 text-primary"></i>Lý do xin nghỉ <span class="text-danger">*</span>
                        </label>
                        <textarea id="reason" name="reason"
                                  class="form-control @error('reason') is-invalid @enderror"
                                  rows="3" maxlength="5000"
                                  placeholder="Nhập lý do xin nghỉ và thông tin bàn giao công việc..."
                                  required>{{ old('reason') }}</textarea>
                        @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-send-fill"></i>
                        {{ auth()->user()->isDirector() ? 'Ghi nhận lịch nghỉ' : 'Gửi đơn nghỉ phép' }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- BẢNG LỊCH SỬ NGHỈ PHÉP CỦA BẢN THÂN --}}
    <div class="col-12 col-xl-7">
        <div class="portal-card h-100 d-flex flex-column">
            <div class="portal-card-header justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <span class="portal-icon portal-icon-teal"><i class="bi bi-clock-history"></i></span>
                    <div>
                        <h2>Lịch sử nghỉ phép của tôi</h2>
                        <p>Danh sách các đơn nghỉ phép bạn đã đăng ký.</p>
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
                            <th>Lý do &amp; Đính kèm</th>
                            <th>Trạng thái</th>
                            <th>Phản hồi</th>
                            <th class="text-end">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($leaveRequests as $leaveRequest)
                            @php
                                $typeInfo = $leaveTypeMap[$leaveRequest->leave_type] ?? ['Khác', 'bi-calendar', 'badge bg-light text-dark'];
                            @endphp
                            <tr>
                                <td>
                                    <div class="fw-bold text-nowrap">{{ $leaveRequest->start_date->format('d/m/Y') }}</div>
                                    <div class="small text-muted text-nowrap">đến {{ $leaveRequest->end_date->format('d/m/Y') }}</div>
                                    <span class="badge bg-light text-primary border mt-1" style="font-size:10.5px;">
                                        {{ rtrim(rtrim(number_format($leaveRequest->total_days, 2), '0'), '.') }} ngày làm việc
                                    </span>
                                    @if($leaveRequest->is_retroactive)
                                        <span class="badge bg-info text-dark mt-1" style="font-size:10px;">Nghỉ bù</span>
                                    @endif
                                </td>

                                <td>
                                    <span class="{{ $typeInfo[2] }}" style="font-size:11.5px;">
                                        <i class="bi {{ $typeInfo[1] }} me-1"></i>{{ $typeInfo[0] }}
                                    </span>
                                </td>

                                <td>
                                    <div class="text-break" style="max-width: 220px; font-size: 13px;">
                                        {{ $leaveRequest->reason }}
                                    </div>
                                    @if($leaveRequest->attachment_path)
                                        <a href="{{ route('leave-requests.attachment', $leaveRequest) }}" class="small text-primary text-decoration-none">
                                            <i class="bi bi-paperclip"></i> Tải giấy xác nhận
                                        </a>
                                    @endif
                                </td>

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
                                    @elseif($leaveRequest->status === 'expired')
                                        <span class="badge bg-secondary">
                                            <i class="bi bi-clock me-1"></i>Quá hạn (Chưa xử lý)
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">{{ $leaveRequest->status }}</span>
                                    @endif
                                </td>

                                <td>
                                    @if($leaveRequest->rejection_reason)
                                        <span class="small text-danger d-block" title="{{ $leaveRequest->rejection_reason }}">
                                            <i class="bi bi-chat-square-quote me-1"></i>{{ Str::limit($leaveRequest->rejection_reason, 30) }}
                                        </span>
                                    @elseif($leaveRequest->status === 'expired')
                                        <span class="small text-muted">Đơn tự động hết hạn do quá ngày bắt đầu nghỉ.</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>

                                <td class="text-end text-nowrap">
                                    @if($leaveRequest->status === 'pending')
                                        <form method="POST"
                                              action="{{ route('user.leave-requests.cancel', $leaveRequest) }}"
                                              onsubmit="return confirm('Bạn có chắc chắn muốn hủy đơn xin nghỉ phép này không?');"
                                              class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2">
                                                <i class="bi bi-trash3 me-1"></i>Hủy đơn
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
