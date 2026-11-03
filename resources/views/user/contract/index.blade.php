@extends('user.layout.masterlayout')
@section('title', 'Hợp đồng của tôi')
@section('description', 'Theo dõi thông tin và thời hạn các hợp đồng lao động đã được phê duyệt của bạn.')

@section('content')
{{-- Page header --}}
<div class="portal-page-heading d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <p class="portal-eyebrow mb-1">Tài liệu &amp; Pháp lý</p>
        <h1 class="h3 fw-bold mb-1">Hợp đồng lao động của tôi</h1>
        <p class="text-muted mb-0">Theo dõi chi tiết chế độ, thời hạn và phụ lục hợp đồng đã ký kết.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="portal-document-number">
            <i class="bi bi-file-earmark-check me-1"></i>{{ $contracts->total() }} hợp đồng đã duyệt
        </span>
    </div>
</div>

<div class="row g-4">
    @forelse($contracts as $contract)
        @php
            $isIndefinite = !$contract->effective_to;
            $isActive = $contract->status === 'active';
            $coeff = $contract->coefficient ?: 1.00;
            $estimatedTotal = ($contract->base_salary * $coeff) + ($contract->allowance ?: 0);
        @endphp
        <div class="col-12 col-xl-6">
            <article class="portal-contract-card h-100 d-flex flex-column" aria-labelledby="contractTitle{{ $loop->index }}">
                {{-- Header --}}
                <div class="portal-contract-top">
                    <div class="d-flex align-items-center gap-3" style="min-width: 0;">
                        <span class="portal-icon"><i class="bi bi-file-earmark-text-fill" aria-hidden="true"></i></span>
                        <div style="min-width: 0;">
                            <div class="portal-eyebrow mb-1">Hợp đồng lao động</div>
                            <h2 class="portal-contract-code text-primary mb-0" id="contractTitle{{ $loop->index }}">
                                {{ $contract->contract_code }}
                            </h2>
                        </div>
                    </div>
                    <div>
                        @if($isActive)
                            <span class="portal-badge portal-badge-success shadow-sm">
                                <i class="bi bi-check-circle-fill" aria-hidden="true"></i>Đang hiệu lực
                            </span>
                        @else
                            <span class="portal-badge portal-badge-neutral">
                                <i class="bi bi-clock-history" aria-hidden="true"></i>Đã phê duyệt
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Dates strip --}}
                <div class="portal-contract-dates flex-grow-0">
                    <div>
                        <small><i class="bi bi-calendar-event me-1 text-primary" aria-hidden="true"></i>Ngày bắt đầu</small>
                        <strong class="portal-number">{{ $contract->effective_from->format('d/m/Y') }}</strong>
                    </div>
                    <div>
                        <small><i class="bi bi-calendar-check me-1 text-primary" aria-hidden="true"></i>Ngày kết thúc</small>
                        <strong class="portal-number">
                            @if($isIndefinite)
                                <span class="badge bg-light text-primary border">Vô thời hạn</span>
                            @else
                                {{ $contract->effective_to->format('d/m/Y') }}
                            @endif
                        </strong>
                    </div>
                </div>

                {{-- Salary Grid --}}
                <dl class="portal-contract-money mb-0 flex-grow-1">
                    <div>
                        <dt>Lương cơ bản</dt>
                        <dd class="portal-number">{{ number_format($contract->base_salary, 0, ',', '.') }} <span class="text-muted fs-6">₫</span></dd>
                    </div>
                    <div>
                        <dt>Hệ số lương</dt>
                        <dd class="portal-number">{{ $contract->coefficient ?: '1.00' }}</dd>
                    </div>
                    <div>
                        <dt>Phụ cấp</dt>
                        <dd class="portal-number">{{ number_format($contract->allowance ?: 0, 0, ',', '.') }} <span class="text-muted fs-6">₫</span></dd>
                    </div>
                </dl>

                @if($contract->allowance_note)
                <div class="px-3 pb-2 small text-muted">
                    <strong>Chi tiết phụ cấp:</strong> {{ $contract->allowance_note }}
                </div>
                @endif

                {{-- Bottom Action (Scan document) --}}
                <div class="p-3 bg-white border-top d-flex align-items-center justify-content-between">
                    <div class="small text-muted">
                        <i class="bi bi-calculator me-1"></i>Tổng thu nhập cứng ước tính: 
                        <strong class="text-primary">{{ number_format($estimatedTotal, 0, ',', '.') }} ₫</strong>
                    </div>
                    @if($contract->file_path)
                        <a class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-1 shadow-sm"
                           href="{{ route('contracts.download-pdf', $contract) }}">
                            <i class="bi bi-file-earmark-pdf-fill text-danger"></i> Tải hợp đồng PDF
                        </a>
                    @else
                        <span class="badge bg-light text-muted border" style="font-size:11px;">Chưa có bản PDF đính kèm</span>
                    @endif
                </div>
            </article>
        </div>
    @empty
        <div class="col-12">
            <div class="portal-card portal-empty">
                <span class="portal-empty-icon"><i class="bi bi-file-earmark-x" aria-hidden="true"></i></span>
                <h2>Chưa có hợp đồng nào được phê duyệt</h2>
                <p>Hợp đồng lao động của bạn sẽ tự động hiển thị tại đây ngay sau khi được Ban Giám đốc và phòng Nhân sự phê duyệt.</p>
            </div>
        </div>
    @endforelse
</div>

{{-- Pagination --}}
<div class="portal-pagination d-flex justify-content-center mt-3">{{ $contracts->links('pagination::bootstrap-5') }}</div>

{{-- Notice strip --}}
<div class="portal-notice mt-4">
    <i class="bi bi-info-circle-fill"></i>
    <div>
        <strong>Lưu ý về hiệu lực:</strong> Các điều khoản và mức lương, phụ cấp được áp dụng chính xác theo từng giai đoạn hiệu lực của hợp đồng. Mọi thắc mắc về hợp đồng xin vui lòng liên hệ phòng Nhân sự.
    </div>
</div>
@endsection
