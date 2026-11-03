@extends('admin.layout.masterlayout')
@section('title', 'Chi tiết đơn nghỉ phép')

@php
    $leaveTypeLabels = [
        'annual' => 'Nghỉ phép năm',
        'sick' => 'Nghỉ ốm',
        'personal' => 'Việc riêng',
        'unpaid' => 'Nghỉ không lương',
    ];
    $isStalePending = $leaveRequest->status === 'pending'
        && $leaveRequest->created_at
        && $leaveRequest->created_at->lte(now()->subDays(2));
@endphp

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="h4 mb-1">Chi tiết đơn nghỉ phép #{{ $leaveRequest->id }}</h1>
        <p class="text-muted small mb-0">Thông tin chi tiết yêu cầu nghỉ phép và lịch sử phê duyệt.</p>
    </div>
    <a class="btn btn-light" href="{{ route('leave-requests.index') }}">Quay lại danh sách</a>
</div>

@if($isStalePending)
<div class="alert alert-warning">
    <i class="bi bi-exclamation-triangle-fill me-1"></i>
    <strong>Cảnh báo:</strong> Đơn nghỉ phép này đã gửi quá 2 ngày ({{ $leaveRequest->created_at->diffForHumans() }}) nhưng chưa được xử lý!
</div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <div class="row g-4">
            <div class="col-lg-6">
                <h2 class="h6 fw-bold text-uppercase text-muted mb-3">Thông tin nhân viên</h2>
                <dl class="row mb-0">
                    <dt class="col-sm-5">Mã nhân viên</dt>
                    <dd class="col-sm-7 fw-semibold">{{ $leaveRequest->employee?->employee_code }}</dd>
                    <dt class="col-sm-5">Họ và tên</dt>
                    <dd class="col-sm-7">{{ $leaveRequest->employee?->full_name }}</dd>
                    <dt class="col-sm-5">Phòng ban</dt>
                    <dd class="col-sm-7">{{ $leaveRequest->employee?->department?->name ?? '—' }}</dd>
                    <dt class="col-sm-5">Chức vụ</dt>
                    <dd class="col-sm-7">{{ $leaveRequest->employee?->position?->name ?? '—' }}</dd>
                </dl>
            </div>

            <div class="col-lg-6">
                <h2 class="h6 fw-bold text-uppercase text-muted mb-3">Thông tin kỳ nghỉ</h2>
                <dl class="row mb-0">
                    <dt class="col-sm-5">Loại nghỉ</dt>
                    <dd class="col-sm-7">
                        {{ $leaveTypeLabels[$leaveRequest->leave_type] ?? $leaveRequest->leave_type }}
                        @if($leaveRequest->is_retroactive)
                            <span class="badge bg-info text-dark ms-1">Nghỉ bù (Quá khứ)</span>
                        @endif
                    </dd>
                    <dt class="col-sm-5">Thời gian nghỉ</dt>
                    <dd class="col-sm-7">{{ $leaveRequest->start_date?->format('d/m/Y') }} &rarr; {{ $leaveRequest->end_date?->format('d/m/Y') }}</dd>
                    <dt class="col-sm-5">Số ngày làm việc</dt>
                    <dd class="col-sm-7 fw-bold text-primary">{{ rtrim(rtrim(number_format($leaveRequest->total_days, 2), '0'), '.') }} ngày (Đã trừ T7, CN)</dd>
                    <dt class="col-sm-5">Người tạo / Ngày gửi</dt>
                    <dd class="col-sm-7">
                        {{ $leaveRequest->creator?->employee?->full_name ?? $leaveRequest->creator?->email ?? 'Nhân viên' }}
                        ({{ $leaveRequest->created_at?->format('d/m/Y H:i') }})
                    </dd>
                    <dt class="col-sm-5">Trạng thái</dt>
                    <dd class="col-sm-7">@include('admin.partial.status', ['status' => $leaveRequest->status])</dd>
                </dl>
            </div>

            <div class="col-12">
                <h2 class="h6 fw-bold mb-2">Lý do xin nghỉ</h2>
                <div class="rounded-3 bg-light p-3 border">{{ $leaveRequest->reason }}</div>
            </div>

            @if($leaveRequest->attachment_path)
            <div class="col-12">
                <h2 class="h6 fw-bold mb-2">Giấy xác nhận / Tài liệu đính kèm</h2>
                <a href="{{ route('leave-requests.attachment', $leaveRequest) }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-download me-1"></i> Tải xuống file đính kèm
                </a>
            </div>
            @endif

            @if($leaveRequest->reviewed_at)
                <div class="col-12">
                    <h2 class="h6 fw-bold mb-2">Thông tin xét duyệt</h2>
                    <div class="rounded-3 border p-3">
                        <strong>Người xử lý:</strong> {{ $leaveRequest->reviewer?->employee?->full_name ?? $leaveRequest->reviewer?->email ?? '—' }}
                        <span class="text-muted"> &bull; {{ $leaveRequest->reviewed_at->format('d/m/Y H:i') }}</span>
                        @if($leaveRequest->rejection_reason)
                            <div class="mt-2 text-danger">
                                <strong>Lý do từ chối:</strong> {{ $leaveRequest->rejection_reason }}
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        @if($leaveRequest->status === 'pending' && auth()->user()->can('approveOrReject', $leaveRequest))
            <hr class="my-4">
            <div class="d-flex flex-wrap gap-2">
                <form method="POST" action="{{ route('leave-requests.approve', $leaveRequest) }}">
                    @csrf
                    <button class="btn btn-success" type="submit">Phê duyệt đơn</button>
                </form>
                <button class="btn btn-outline-danger" type="button" data-bs-toggle="collapse" data-bs-target="#rejectForm">
                    Từ chối đơn
                </button>
            </div>
            <form id="rejectForm" class="collapse mt-3" method="POST" action="{{ route('leave-requests.reject', $leaveRequest) }}">
                @csrf
                <label class="form-label" for="rejection_reason">Lý do từ chối <span class="text-danger">*</span></label>
                <textarea id="rejection_reason" name="rejection_reason" class="form-control mb-2" rows="3" required>{{ old('rejection_reason') }}</textarea>
                <button class="btn btn-danger" type="submit">Xác nhận từ chối</button>
            </form>
        @endif
    </div>
</div>
@endsection
