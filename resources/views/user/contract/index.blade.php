@extends('user.layout.masterlayout')
@section('title', 'Hợp đồng của tôi')
@section('description', 'Theo dõi thông tin và thời hạn các hợp đồng lao động đã được phê duyệt của bạn.')

@section('content')
<div class="portal-document-heading mb-3">
    <div class="d-flex align-items-center gap-2">
        <span class="portal-icon"><i class="bi bi-folder2-open" aria-hidden="true"></i></span>
        <span class="fw-semibold">Tài liệu của bạn</span>
    </div>
    <span class="portal-document-number">{{ $contracts->count() }} hợp đồng trên trang này</span>
</div>

<div class="row g-4">
    @forelse($contracts as $contract)
        <div class="col-12 col-xl-6">
            <article class="portal-card h-100 d-flex flex-column" aria-labelledby="contractTitle{{ $loop->index }}">
                <div class="portal-card-header justify-content-between">
                    <div class="d-flex align-items-center gap-3" style="min-width: 0;">
                        <span class="portal-icon"><i class="bi bi-file-earmark-text" aria-hidden="true"></i></span>
                        <div style="min-width: 0;">
                            <div class="portal-eyebrow mb-1">Mã hợp đồng</div>
                            <h2 class="portal-contract-code" id="contractTitle{{ $loop->index }}">{{ $contract->contract_code }}</h2>
                        </div>
                    </div>
                    <span class="portal-badge portal-badge-success"><i class="bi bi-check-circle" aria-hidden="true"></i>Đã phê duyệt</span>
                </div>
                <div class="portal-contract-dates flex-grow-1">
                    <div><small><i class="bi bi-calendar-event me-1" aria-hidden="true"></i>Ngày bắt đầu</small><strong class="portal-number">{{ $contract->effective_from->format('d/m/Y') }}</strong></div>
                    <div><small><i class="bi bi-calendar-check me-1" aria-hidden="true"></i>Ngày kết thúc</small><strong class="portal-number">{{ $contract->effective_to?->format('d/m/Y') ?? 'Không xác định thời hạn' }}</strong></div>
                </div>
                <dl class="portal-contract-money mb-0">
                    <div><dt>Lương cơ bản</dt><dd class="portal-number">{{ number_format($contract->base_salary, 2, ',', '.') }} ₫</dd></div>
                    <div><dt>Hệ số lương</dt><dd class="portal-number">{{ $contract->salary_coefficient }}</dd></div>
                    <div><dt>Phụ cấp</dt><dd class="portal-number">{{ number_format($contract->allowance, 2, ',', '.') }} ₫</dd></div>
                </dl>
            </article>
        </div>
    @empty
        <div class="col-12">
            <div class="portal-card portal-empty">
                <span class="portal-empty-icon"><i class="bi bi-file-earmark-text" aria-hidden="true"></i></span>
                <h2>Chưa có hợp đồng đã duyệt</h2>
                <p>Hợp đồng lao động của bạn sẽ xuất hiện tại đây sau khi được phê duyệt.</p>
            </div>
        </div>
    @endforelse
</div>

<div class="portal-pagination">{{ $contracts->links('pagination::bootstrap-5') }}</div>
<div class="portal-notice mt-4"><i class="bi bi-info-circle" aria-hidden="true"></i><span>Ngày hiệu lực trên từng hợp đồng quyết định thời gian áp dụng các điều khoản.</span></div>
@endsection
