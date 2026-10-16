@extends('user.layout.masterlayout')
@section('title', 'Bảng lương của tôi')
@section('description', 'Xem thu nhập, các khoản khấu trừ và số tiền thực nhận theo từng kỳ lương đã công bố.')

@section('content')
@forelse($details as $detail)
    <article class="portal-card portal-payslip" aria-labelledby="payrollPeriod{{ $loop->index }}">
        <div class="portal-payslip-header">
            <div>
                <div class="portal-eyebrow">Phiếu lương cá nhân</div>
                <h2 class="portal-period-title" id="payrollPeriod{{ $loop->index }}">Tháng {{ $detail->period->month }}/{{ $detail->period->year }}</h2>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <span class="portal-badge portal-badge-success"><i class="bi bi-check-circle" aria-hidden="true"></i>Đã công bố</span>
                    <span class="small portal-muted">Hợp đồng: <strong>{{ $detail->contract->contract_code }}</strong></span>
                </div>
            </div>
            <div class="portal-net">
                <span class="portal-net-label">THỰC NHẬN</span>
                <div class="portal-net-amount portal-number">{{ $detail->net_salary !== null ? number_format($detail->net_salary, 2, ',', '.').' ₫' : 'Chưa cập nhật' }}</div>
            </div>
        </div>

        <div class="row g-0 gy-4 py-4">
            @foreach([
                'Cơ sở tính lương' => ['base_salary' => 'Lương cơ bản', 'salary_coefficient' => 'Hệ số', 'standard_days' => 'Công chuẩn', 'actual_days' => 'Công thực tế'],
                'Thu nhập' => ['allowance' => 'Phụ cấp', 'overtime_pay' => 'Tiền tăng ca', 'gross_salary' => 'Tổng lương (Gross)'],
                'Khấu trừ & kết quả' => ['deduction' => 'Khấu trừ', 'net_salary' => 'Thực nhận (Net)'],
            ] as $groupLabel => $fields)
                <div class="col-12 col-md-6 col-xl-4">
                    <section class="portal-payroll-group">
                        <h3>{{ $groupLabel }}</h3>
                        <dl class="portal-payroll-lines">
                            @foreach($fields as $key => $label)
                                <div class="{{ in_array($key, ['gross_salary', 'net_salary']) ? 'portal-payroll-total' : '' }}">
                                    <dt>{{ $label }}</dt>
                                    <dd class="portal-number">
                                        @if($detail->$key === null)
                                            Chưa cập nhật
                                        @elseif(in_array($key, ['base_salary', 'allowance', 'overtime_pay', 'deduction', 'gross_salary', 'net_salary']))
                                            {{ number_format($detail->$key, 2, ',', '.') }} ₫
                                        @else
                                            {{ $detail->$key }}
                                            @if(in_array($key, ['standard_days', 'actual_days']))
                                                công
                                            @endif
                                        @endif
                                    </dd>
                                </div>
                            @endforeach
                        </dl>
                    </section>
                </div>
            @endforeach
        </div>

        <details class="portal-overtime">
            <summary><i class="bi bi-clock-history me-2" aria-hidden="true"></i>Căn cứ tăng ca</summary>
            @if(empty($detail->calculation_snapshot['overtime'] ?? []))
                <p class="small portal-muted p-3 mb-0">Kỳ lương này chưa có chi tiết căn cứ tăng ca.</p>
            @else
                <div class="table-responsive portal-table-scroll" role="region" aria-label="Chi tiết căn cứ tăng ca, cuộn ngang để xem đầy đủ" tabindex="0">
                    <table class="table table-hover portal-table">
                        <caption class="visually-hidden">Căn cứ tăng ca đã lưu trong kỳ lương.</caption>
                        <thead><tr><th scope="col">Ngày</th><th scope="col">Thời gian</th><th scope="col">Lương giờ</th><th scope="col">Hệ số</th><th scope="col" class="text-end">Thành tiền</th></tr></thead>
                        <tbody>
                            @foreach($detail->calculation_snapshot['overtime'] ?? [] as $basis)
                                <tr>
                                    <td class="text-nowrap portal-number">{{ $basis['date'] }}</td>
                                    <td class="portal-number">{{ $basis['minutes'] }} phút</td>
                                    <td class="text-nowrap portal-number">{{ number_format($basis['hourly_rate'], 2, ',', '.') }} ₫</td>
                                    <td class="portal-number">{{ $basis['multiplier'] }}</td>
                                    <td class="text-end text-nowrap fw-semibold portal-number">{{ number_format($basis['amount'], 2, ',', '.') }} ₫</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </details>
        <p class="portal-payslip-note"><i class="bi bi-chat-left-text me-2" aria-hidden="true"></i><strong>Ghi chú:</strong> {{ $detail->note ?: 'Chưa có ghi chú.' }}</p>
    </article>
@empty
    <div class="portal-card portal-empty">
        <span class="portal-empty-icon"><i class="bi bi-wallet2" aria-hidden="true"></i></span>
        <h2>Chưa có bảng lương được công bố</h2>
        <p>Khi kỳ lương được công bố, bạn có thể xem chi tiết thu nhập và số tiền thực nhận tại đây.</p>
    </div>
@endforelse
<div class="portal-pagination">{{ $details->links('pagination::bootstrap-5') }}</div>
@endsection
