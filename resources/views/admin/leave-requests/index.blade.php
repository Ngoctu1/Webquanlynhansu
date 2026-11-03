@extends('admin.layout.masterlayout')
@section('title', 'Quản lý nghỉ phép & nghỉ bù')

@php
    $statusLabels = [
        'pending' => ['Chờ duyệt', 'bg-warning text-dark'],
        'approved' => ['Đã duyệt', 'bg-success'],
        'rejected' => ['Đã từ chối', 'bg-danger'],
        'cancelled' => ['Đã hủy', 'bg-secondary'],
        'expired' => ['Quá hạn (Chưa xử lý)', 'bg-dark'],
    ];

    $leaveTypeLabels = [
        'annual' => 'Nghỉ phép năm',
        'sick' => 'Nghỉ ốm',
        'personal' => 'Việc riêng',
        'unpaid' => 'Nghỉ không lương',
    ];
@endphp

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h1 class="h4 mb-1">Quản lý đăng ký nghỉ phép &amp; nghỉ bù</h1>
        <p class="text-muted small mb-0">
            Mặc định hiển thị các đơn nghỉ phép trong tuần hiện tại và các đơn đang chờ phê duyệt.
        </p>
    </div>

    <div class="d-flex gap-2">
        @can('createRetroactive', \App\Models\LeaveRequest::class)
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#retroactiveLeaveModal">
                + Nghỉ bù (Nhập nghỉ phép quá khứ)
            </button>
        @endcan
    </div>
</div>

{{-- Thống kê nhanh --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="card p-3">
            <div class="text-muted small">Tổng đơn (Theo bộ lọc)</div>
            <div class="h4 fw-bold mb-0">{{ number_format((int) ($stats->total ?? 0)) }}</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card p-3 border-start border-4 border-warning">
            <div class="text-muted small">Đang chờ duyệt</div>
            <div class="h4 fw-bold text-warning-emphasis mb-0">{{ number_format((int) ($stats->pending ?? 0)) }}</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card p-3 border-start border-4 border-success">
            <div class="text-muted small">Đã phê duyệt</div>
            <div class="h4 fw-bold text-success mb-0">{{ number_format((int) ($stats->approved ?? 0)) }}</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card p-3 border-start border-4 border-danger">
            <div class="text-muted small">Từ chối / Quá hạn</div>
            <div class="h4 fw-bold text-danger mb-0">{{ number_format((int) (($stats->rejected ?? 0) + ($stats->expired ?? 0))) }}</div>
        </div>
    </div>
</div>

{{-- Bộ lọc --}}
<div class="card p-3 mb-4">
    <form method="GET" action="{{ route('leave-requests.index') }}" class="row g-2 align-items-end">
        <div class="col-md-2">
            <label class="form-label small text-muted mb-1">Tìm nhân viên</label>
            <input name="q" class="form-control" value="{{ $filters['q'] ?? '' }}" placeholder="Mã NV hoặc Họ tên...">
        </div>

        <div class="col-md-2">
            <label class="form-label small text-muted mb-1">Phạm vi tuần</label>
            <select name="week" class="form-select">
                <option value="current" @selected(($weekFilter ?? 'current') === 'current')>Tuần hiện tại (Mặc định)</option>
                <option value="all" @selected(($weekFilter ?? '') === 'all')>Tất cả các tuần</option>
            </select>
        </div>

        <div class="col-md-2">
            <label class="form-label small text-muted mb-1">Phòng ban</label>
            <select name="department_id" class="form-select">
                <option value="">Tất cả phòng ban</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" @selected((string) ($filters['department_id'] ?? '') === (string) $dept->id)>{{ $dept->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2">
            <label class="form-label small text-muted mb-1">Trạng thái</label>
            <select name="status" class="form-select">
                <option value="">Tất cả trạng thái</option>
                @foreach($statusLabels as $val => [$label])
                    <option value="{{ $val }}" @selected(($filters['status'] ?? '') === $val)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2">
            <label class="form-label small text-muted mb-1">Loại nghỉ</label>
            <select name="leave_type" class="form-select">
                <option value="">Tất cả loại nghỉ</option>
                @foreach($leaveTypeLabels as $val => $label)
                    <option value="{{ $val }}" @selected(($filters['leave_type'] ?? '') === $val)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2 d-flex gap-2">
            <button class="btn btn-outline-primary w-100" type="submit">Lọc</button>
            <a class="btn btn-light" href="{{ route('leave-requests.index') }}">Đặt lại</a>
        </div>
    </form>
</div>

{{-- Bảng danh sách --}}
<div class="card table-responsive">
    <table class="table align-middle mb-0">
        <thead>
            <tr>
                <th>Nhân viên</th>
                <th>Phòng ban</th>
                <th>Loại nghỉ</th>
                <th>Thời gian nghỉ</th>
                <th>Số ngày</th>
                <th>Lý do &amp; File</th>
                <th>Ngày gửi</th>
                <th>Trạng thái</th>
                <th class="text-end">Thao tác</th>
            </tr>
        </thead>
        <tbody>
            @forelse($leaveRequests as $leaveRequest)
                @php
                    $isStalePending = $leaveRequest->status === 'pending'
                        && $leaveRequest->created_at
                        && $leaveRequest->created_at->lte(now()->subDays(2));
                    $badge = $statusLabels[$leaveRequest->status] ?? [$leaveRequest->status, 'bg-secondary'];
                @endphp
                <tr @if($isStalePending) class="table-warning" @endif>
                    <td>
                        <div class="fw-semibold">{{ $leaveRequest->employee?->full_name }}</div>
                        <div class="small text-muted">{{ $leaveRequest->employee?->employee_code }}</div>
                    </td>
                    <td>
                        <div>{{ $leaveRequest->employee?->department?->name ?? '—' }}</div>
                        <small class="text-muted">{{ $leaveRequest->employee?->position?->name ?? '—' }}</small>
                    </td>
                    <td>
                        <div>{{ $leaveTypeLabels[$leaveRequest->leave_type] ?? $leaveRequest->leave_type }}</div>
                        @if($leaveRequest->is_retroactive)
                            <span class="badge bg-info text-dark mt-1">Nghỉ bù (Quá khứ)</span>
                        @endif
                    </td>
                    <td class="text-nowrap">
                        <div>{{ $leaveRequest->start_date?->format('d/m/Y') }}</div>
                        <small class="text-muted">&rarr; {{ $leaveRequest->end_date?->format('d/m/Y') }}</small>
                    </td>
                    <td class="fw-bold">
                        {{ rtrim(rtrim(number_format($leaveRequest->total_days, 2), '0'), '.') }} ngày
                    </td>
                    <td style="max-width: 220px;">
                        <div class="text-truncate" title="{{ $leaveRequest->reason }}">{{ $leaveRequest->reason }}</div>
                        @if($leaveRequest->attachment_path)
                            <a href="{{ route('leave-requests.attachment', $leaveRequest) }}" class="small text-primary">
                                <i class="bi bi-paperclip"></i> File đính kèm
                            </a>
                        @endif
                    </td>
                    <td>
                        <div>{{ $leaveRequest->created_at?->format('d/m/Y H:i') }}</div>
                        @if($isStalePending)
                            <span class="badge bg-danger mt-1">
                                <i class="bi bi-exclamation-triangle-fill"></i> Quá 2 ngày chưa xử lý!
                            </span>
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $badge[1] }}">{{ $badge[0] }}</span>
                        @if($leaveRequest->rejection_reason)
                            <div class="small text-danger mt-1" title="{{ $leaveRequest->rejection_reason }}">
                                Lý do: {{ Str::limit($leaveRequest->rejection_reason, 30) }}
                            </div>
                        @endif
                    </td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('leave-requests.show', $leaveRequest) }}" class="btn btn-sm btn-outline-primary">Chi tiết</a>

                        @if($leaveRequest->status === 'pending')
                            @can('approveOrReject', $leaveRequest)
                                <form class="d-inline" method="POST" action="{{ route('leave-requests.approve', $leaveRequest) }}">
                                    @csrf
                                    <button class="btn btn-sm btn-success" type="submit">Duyệt</button>
                                </form>
                                <button class="btn btn-sm btn-outline-danger" type="button" data-bs-toggle="modal" data-bs-target="#leaveReject{{ $leaveRequest->id }}">
                                    Từ chối
                                </button>
                            @else
                                <span class="badge bg-light text-muted border ms-1">Chờ Giám đốc duyệt</span>
                            @endcan
                        @endif
                    </td>
                </tr>

                @if($leaveRequest->status === 'pending' && auth()->user()->can('approveOrReject', $leaveRequest))
                    <div class="modal fade" id="leaveReject{{ $leaveRequest->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form method="POST" action="{{ route('leave-requests.reject', $leaveRequest) }}">
                                    @csrf
                                    <div class="modal-header">
                                        <h5 class="modal-title">Từ chối đơn nghỉ phép — {{ $leaveRequest->employee?->full_name }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <label class="form-label">Lý do từ chối <span class="text-danger">*</span></label>
                                        <textarea name="rejection_reason" class="form-control" rows="3" required placeholder="Nhập lý do từ chối..."></textarea>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                                        <button type="submit" class="btn btn-danger">Xác nhận từ chối</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
            @empty
                <tr>
                    <td colspan="9" class="text-center py-4 text-muted">Không có đơn nghỉ phép nào phù hợp.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-3">
    {{ $leaveRequests->links('pagination::bootstrap-5') }}
</div>

{{-- Modal Nhập Nghỉ bù (Quá khứ) dành cho HR & Director --}}
@can('createRetroactive', \App\Models\LeaveRequest::class)
<div class="modal fade" id="retroactiveLeaveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('leave-requests.retroactive.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title">Nhập Nghỉ bù / Nghỉ phép trong quá khứ</h5>
                        <small class="text-muted">
                            Bỏ qua quy tắc báo trước 7 ngày và giới hạn 2 đơn/tháng.
                            {{ auth()->user()->isHR() ? 'Đơn do HR nhập sẽ chuyển chờ Giám đốc phê duyệt.' : 'Đơn do Giám đốc nhập sẽ được phê duyệt ngay.' }}
                        </small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nhân viên <span class="text-danger">*</span></label>
                            <select name="employee_id" class="form-select" required>
                                <option value="">-- Chọn nhân viên --</option>
                                @foreach($eligibleEmployees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->employee_code }} — {{ $emp->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Loại nghỉ <span class="text-danger">*</span></label>
                            <select name="leave_type" class="form-select" required>
                                @foreach($leaveTypeLabels as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Từ ngày (Quá khứ / Hôm nay) <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" class="form-control" max="{{ today()->toDateString() }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Đến ngày (Quá khứ / Hôm nay) <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" class="form-control" max="{{ today()->toDateString() }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">File minh chứng / Giấy xác nhận (nếu có)</label>
                            <input type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Lý do nghỉ bù <span class="text-danger">*</span></label>
                            <textarea name="reason" class="form-control" rows="3" required placeholder="Nhập lý do bổ sung đơn nghỉ phép trong quá khứ..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">
                        {{ auth()->user()->isHR() ? 'Lưu & Trình Giám đốc duyệt' : 'Lưu & Phê duyệt ngay' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan
@endsection
