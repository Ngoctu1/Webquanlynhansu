@extends('admin.layout.masterlayout')
@section('title', 'Chi tiết hợp đồng lao động')

@php
    $contractTypes = [
        'probation' => 'Thử việc',
        'fixed_term' => 'Có thời hạn',
        'indefinite' => 'Không xác định thời hạn',
    ];
    $empUser = $contract->employee?->user;
    $canActivateAccount = $contract->approval_status === 'approved'
        && $empUser
        && $empUser->status === 'pending_reset';
@endphp

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h1 class="h4 mb-1">Chi tiết hợp đồng: {{ $contract->contract_code }}</h1>
        <p class="text-muted small mb-0">
            Nhân viên: <a href="{{ route('employees.show', $contract->employee) }}" class="fw-semibold">{{ $contract->employee?->full_name }} ({{ $contract->employee?->employee_code }})</a>
        </p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @can('update', $contract)
            <a href="{{ route('contracts.edit', $contract) }}" class="btn btn-outline-primary">
                Chỉnh sửa hợp đồng
            </a>
        @endcan
        @can('create', \App\Models\Contract::class)
            <a href="{{ route('contracts.create', ['employee_id' => $contract->employee_id]) }}" class="btn btn-outline-secondary">
                + Gia hạn / Tạo HĐ tiếp theo
            </a>
        @endcan
        <a href="{{ route('contracts.index') }}" class="btn btn-light">Quay lại danh sách</a>
    </div>
</div>

@if($errors->any())
<div class="alert alert-danger mb-3">
    <ul class="mb-0 ps-3">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

@if($contract->approval_status === 'rejected' && $contract->rejection_reason)
<div class="alert alert-danger">
    <strong>Hợp đồng bị từ chối phê duyệt:</strong> {{ $contract->rejection_reason }}
    @if(auth()->user()->isHR())
        <div class="mt-1 small">Vui lòng bấm <strong>"Chỉnh sửa hợp đồng"</strong> để cập nhật lại thông tin và trình Giám đốc phê duyệt lại.</div>
    @endif
</div>
@endif

@if(isset($pendingChangeRequest) && $pendingChangeRequest)
<div class="alert alert-warning d-flex justify-content-between align-items-center">
    <div>
        <strong>Đang có đề xuất chỉnh sửa chờ duyệt:</strong>
        Hợp đồng này có một yêu cầu cập nhật từ HR đang chờ Giám đốc phê duyệt.
    </div>
    @if(auth()->user()->isDirector())
        <a href="{{ route('change-requests.index') }}" class="btn btn-sm btn-warning">Xem tại Trung tâm phê duyệt</a>
    @endif
</div>
@endif

<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card p-4 h-100">
            <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-3">
                <div>
                    <h2 class="h5 mb-1">{{ $contract->contract_code }} — {{ $contractTypes[$contract->contract_type] ?? $contract->contract_type }}</h2>
                    <div class="text-muted small">
                        Phòng ban: {{ $contract->employee?->department?->name ?? '—' }} |
                        Chức vụ: {{ $contract->employee?->position?->name ?? '—' }}
                    </div>
                </div>
                <div class="d-flex gap-2">
                    @include('admin.partial.status', ['status' => $contract->status])
                    @include('admin.partial.status', ['status' => $contract->approval_status])
                </div>
            </div>

            <div class="row g-3">
                <div class="col-sm-4">
                    <div class="text-muted small">Ngày ký hợp đồng</div>
                    <div class="fw-semibold">{{ $contract->signed_date?->format('d/m/Y') ?? '—' }}</div>
                </div>
                <div class="col-sm-4">
                    <div class="text-muted small">Ngày bắt đầu hiệu lực</div>
                    <div class="fw-semibold">{{ $contract->effective_from?->format('d/m/Y') }}</div>
                </div>
                <div class="col-sm-4">
                    <div class="text-muted small">Ngày hết hạn</div>
                    <div class="fw-semibold">{{ $contract->effective_to?->format('d/m/Y') ?? 'Không xác định thời hạn' }}</div>
                </div>

                <div class="col-sm-4">
                    <div class="text-muted small">Lương cơ bản</div>
                    <div class="fw-bold text-primary">{{ number_format($contract->base_salary, 0, ',', '.') }} ₫</div>
                </div>
                <div class="col-sm-4">
                    <div class="text-muted small">Hệ số lương</div>
                    <div class="fw-semibold">{{ number_format($contract->coefficient, 2) }}</div>
                </div>
                <div class="col-sm-4">
                    <div class="text-muted small">Tổng phụ cấp</div>
                    <div class="fw-bold text-success">{{ number_format($contract->allowance, 0, ',', '.') }} ₫</div>
                </div>

                <div class="col-12">
                    <div class="text-muted small">Ghi chú phụ cấp (Chi tiết các khoản phụ cấp)</div>
                    <div class="p-2 bg-light rounded border mt-1">
                        {{ $contract->allowance_note ?: 'Không có ghi chú phụ cấp chi tiết.' }}
                    </div>
                </div>

                <div class="col-sm-6">
                    <div class="text-muted small">Hợp đồng liền trước (Chuỗi gia hạn)</div>
                    <div>
                        @if($contract->previousContract)
                            <a href="{{ route('contracts.show', $contract->previousContract) }}" class="fw-semibold">
                                {{ $contract->previousContract->contract_code }}
                            </a>
                            <span class="text-muted small">({{ $contract->previousContract->effective_from?->format('d/m/Y') }})</span>
                        @else
                            <span class="text-muted">Không có (Hợp đồng đầu tiên)</span>
                        @endif
                    </div>
                </div>

                <div class="col-sm-6">
                    <div class="text-muted small">Thông tin phê duyệt</div>
                    <div>
                        @if($contract->approver)
                            {{ $contract->approver->employee?->full_name ?? $contract->approver->email }}
                            <span class="text-muted small">({{ $contract->approved_at?->format('d/m/Y H:i') }})</span>
                        @else
                            <span class="text-muted">Chưa phê duyệt</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        {{-- Khu vực File PDF Hợp đồng đã ký giấy --}}
        <div class="card p-4 mb-3">
            <h3 class="h6 mb-2">File PDF hợp đồng đã ký giấy</h3>
            <p class="text-muted small mb-3">
                Lưu trữ bảo mật tại <code>storage/app/private/contracts/</code>. Mọi lượt tải file đều được ghi nhận vào Nhật ký kiểm toán (Audit Log).
            </p>

            @if($contract->file_path)
                <div class="mb-3">
                    <a href="{{ route('contracts.download-pdf', $contract) }}" class="btn btn-primary w-100">
                        Tải xuống File PDF Hợp đồng
                    </a>
                </div>
            @else
                <div class="alert alert-warning py-2 small mb-3">
                    Chưa có file PDF hợp đồng đã ký giấy đính kèm. (Bắt buộc phải có file PDF mới có thể phê duyệt hợp đồng).
                </div>
            @endif

            @can('update', $contract)
                <form method="POST" action="{{ route('contracts.signed-document.store', $contract) }}" enctype="multipart/form-data" class="border-top pt-3">
                    @csrf
                    <label class="form-label small fw-semibold">
                        {{ $contract->file_path ? 'Cập nhật / Thay thế file PDF đã ký' : 'Đính kèm file PDF đã ký (Bắt buộc để duyệt)' }}
                    </label>
                    <input type="file" name="pdf_file" accept=".pdf,application/pdf" class="form-control form-control-sm mb-2" required>
                    <button type="submit" class="btn btn-sm btn-outline-secondary w-100">
                        Tải lên file PDF
                    </button>
                </form>
            @endcan
        </div>

        {{-- Khu vực Phê duyệt / Từ chối dành riêng cho Giám đốc --}}
        @can('approve', $contract)
            @if($contract->approval_status === 'pending_approval')
                <div class="card p-4 border-primary mb-3">
                    <h3 class="h6 mb-2 text-primary">Phê duyệt hợp đồng (Giám đốc)</h3>
                    <p class="small text-muted mb-3">
                        Khi bấm <strong>Phê duyệt</strong>, hợp đồng này sẽ chuyển sang <code>active</code> và tự động chuyển hợp đồng đang hiệu lực cũ (nếu có) của nhân viên sang <code>expired</code>.
                    </p>

                    @if(empty($contract->file_path))
                        <div class="alert alert-danger py-2 small">
                            Chưa thể phê duyệt vì hợp đồng chưa có file PDF đã ký giấy đính kèm.
                        </div>
                    @endif

                    <form method="POST" action="{{ route('contracts.approve', $contract) }}" class="mb-3">
                        @csrf
                        <button class="btn btn-success w-100" type="submit" @disabled(empty($contract->file_path))>
                            Phê duyệt &amp; Kích hoạt hợp đồng
                        </button>
                    </form>

                    <form method="POST" action="{{ route('contracts.reject', $contract) }}" class="border-top pt-3">
                        @csrf
                        <label for="rejection_reason" class="form-label small fw-semibold">Lý do từ chối <span class="text-danger">*</span></label>
                        <textarea id="rejection_reason" name="rejection_reason" class="form-control form-control-sm mb-2" rows="2" required placeholder="Nhập lý do từ chối để HR chỉnh sửa...">{{ old('rejection_reason') }}</textarea>
                        <button class="btn btn-outline-danger btn-sm w-100" type="submit">Từ chối hợp đồng</button>
                    </form>
                </div>
            @endif
        @endcan

        {{-- Khu vực Gửi mail kích hoạt tài khoản sau khi hợp đồng được duyệt --}}
        @if($canActivateAccount && auth()->user()->can('sendActivation', $empUser))
            <div class="card p-4 border-success">
                <h3 class="h6 text-success mb-2">Kích hoạt tài khoản nhân viên</h3>
                <p class="small text-muted mb-3">
                    Hợp đồng đã được Giám đốc phê duyệt. Bạn có thể gửi email kích hoạt tài khoản cho nhân viên ({{ $empUser->email }}).
                </p>
                <form method="POST" action="{{ route('users.send-activation', $empUser) }}">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm w-100">
                        Gửi mail kích hoạt tài khoản
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>
@endsection
