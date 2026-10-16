@extends('admin.layout.masterlayout')
@section('title', 'Chi tiết kỳ lương')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4"><h1 class="h4 mb-0">Chi tiết kỳ lương</h1></div>
<div class="card p-4 mb-3"><h2 class="h5">Tháng {{ $period->month }}/{{ $period->year }} — Công chuẩn {{ $period->standard_work_days }}</h2><p>@include('admin.partial.status', ['status'=>$period->status])</p>
@if($period->rejection_reason)<div class="alert alert-warning">Lý do từ chối: {{ $period->rejection_reason }}</div>@endif
<p>Lương theo công = lương cơ bản × hệ số × công thực tế / công chuẩn, làm tròn đến 2 chữ số thập phân. Tổng lương = lương theo công + phụ cấp + tiền tăng ca. Thực nhận = tổng lương − khấu trừ.</p></div>
<div class="card table-responsive mb-3"><table class="table table-sm align-middle"><thead><tr><th>Nhân viên</th><th>Hợp đồng</th><th>Lương cơ bản</th><th>Hệ số</th><th>Phụ cấp</th><th>Công</th><th>Tăng ca</th><th>Khấu trừ</th><th>Tổng lương</th><th>Thực nhận</th><th>Ghi chú</th></tr></thead><tbody>
@forelse($period->details as $detail)<tr><td>{{ $detail->employee->employee_code }} — {{ $detail->employee->full_name }}</td><td>{{ $detail->contract->contract_code }}</td><td>{{ number_format($detail->base_salary, 2) }}</td><td>{{ $detail->salary_coefficient }}</td><td>{{ number_format($detail->allowance, 2) }}</td><td>{{ $detail->actual_days }}/{{ $detail->standard_days }}</td><td>{{ number_format($detail->overtime_pay, 2) }}</td><td>{{ number_format($detail->deduction, 2) }}</td><td>{{ number_format($detail->gross_salary, 2) }}</td><td>{{ number_format($detail->net_salary, 2) }}</td><td>{{ $detail->note }}<details><summary>Căn cứ snapshot</summary><p>Tổng phút công: {{ $detail->calculation_snapshot['source']['regular_minutes'] ?? '—' }}</p>
@foreach($detail->calculation_snapshot['overtime'] ?? [] as $basis)<p>{{ $basis['date'] }}: {{ $basis['minutes'] }} phút × {{ $basis['hourly_rate'] }}/giờ × {{ $basis['multiplier'] }} = {{ $basis['amount'] }}. {{ $basis['reason'] }}</p>@endforeach</details></td></tr>@empty<tr><td colspan="11">Chưa tính lương.</td></tr>@endforelse</tbody></table></div>
@if(auth()->user()->canManageHrm())@if(in_array($period->status,['draft','rejected']))
<div class="card p-4 mb-3"><h2 class="h5">Nhập khoản điều chỉnh và tính lại</h2><p>Nhập 0 nếu không phát sinh. Khấu trừ phải có căn cứ. OT được tính từ phút đã duyệt và lương giờ/hệ số đã xác nhận bên dưới.</p>
<form method="POST" action="{{ route('payrolls.calculate', $period) }}">@csrf
<div class="table-responsive"><table class="table"><thead><tr><th>Nhân viên</th><th>Khấu trừ</th><th>Căn cứ / ghi chú</th></tr></thead><tbody>
@foreach($employees as $employee)
@php($snapshot = $period->details->firstWhere('employee_id', $employee->id))
<tr><td>{{ $employee->employee_code }} — {{ $employee->full_name }}</td><td><input class="form-control" aria-label="Khấu trừ {{ $employee->employee_code }}" type="number" min="0" step="0.01" name="adjustments[{{ $employee->id }}][deduction]" value="{{ old('adjustments.'.$employee->id.'.deduction', $snapshot?->deduction) }}" required></td><td><input class="form-control" aria-label="Ghi chú {{ $employee->employee_code }}" name="adjustments[{{ $employee->id }}][note]" maxlength="255" value="{{ old('adjustments.'.$employee->id.'.note', $snapshot?->note) }}"></td></tr>
@endforeach</tbody></table></div>
@if($overtimeRows->isNotEmpty())
<h3 class="h6 mt-4">Căn cứ OT theo từng ngày</h3>
<p class="small text-muted">Gợi ý lương giờ = (lương cơ bản × hệ số + phụ cấp hợp đồng) / giờ chuẩn tháng. HR phải kiểm tra tiền lương thực trả và các khoản phụ cấp được tính; loại trừ khoản hỗ trợ không thuộc căn cứ OT. Nếu sửa gợi ý hoặc hệ số, phải ghi lý do. Hệ số tối thiểu: ngày thường 1,5; nghỉ hằng tuần/nghỉ bù 2; lễ/nghỉ hưởng lương 3. Chưa hỗ trợ OT ban đêm.</p>
<div class="table-responsive"><table class="table"><thead><tr><th>Ngày / nhân viên / loại / phút</th><th>Lương giờ</th><th>Hệ số</th><th>Lý do điều chỉnh</th><th>Xác nhận</th></tr></thead><tbody>
@foreach($overtimeRows as $ot)
@php($savedOt = collect($period->details->firstWhere('employee_id', $ot->employee_id)?->calculation_snapshot['overtime'] ?? [])->firstWhere('attendance_id', $ot->id))
<tr><td>{{ $ot->work_date->format('d/m/Y') }} — {{ $ot->employee->full_name }}<br>{{ $ot->overtime_type }} / {{ $ot->overtime_minutes }} phút</td>
<td><input class="form-control" type="number" min="0.01" step="0.01" name="overtime[{{ $ot->id }}][hourly_rate]" aria-label="Lương giờ OT" value="{{ old('overtime.'.$ot->id.'.hourly_rate', $savedOt['hourly_rate'] ?? $ot->suggested_hourly_rate) }}" required></td>
<td><input class="form-control" type="number" min="{{ $ot->minimum_multiplier }}" step="0.001" name="overtime[{{ $ot->id }}][multiplier]" aria-label="Hệ số OT" value="{{ old('overtime.'.$ot->id.'.multiplier', $savedOt['multiplier'] ?? $ot->minimum_multiplier) }}" required></td>
<td><textarea class="form-control" name="overtime[{{ $ot->id }}][reason]" maxlength="1000" aria-label="Lý do thay đổi OT">{{ old('overtime.'.$ot->id.'.reason', $savedOt['reason'] ?? '') }}</textarea></td>
<td><label><input type="checkbox" name="overtime[{{ $ot->id }}][confirmed]" value="1" required> Xác nhận loại ngày, lương giờ thực trả và hệ số</label></td></tr>
@endforeach</tbody></table></div>@endif
<label class="d-block mb-3"><input type="checkbox" name="attendance_confirmed" value="1" required> Tôi đã kiểm tra đủ ngày công tháng này, kể cả ngày không có bản ghi và các khoản điều chỉnh.</label>
<button class="btn btn-primary">Tính và lưu snapshot</button></form></div>
@if($period->details->isNotEmpty())<form method="POST" action="{{ route('payrolls.submit', $period) }}" class="mb-3">@csrf<button class="btn btn-primary">Trình Director duyệt</button></form>@endif
@endif @endif
@if(auth()->user()->canApprove())@if($period->status==='pending_approval')<div class="card p-4"><form method="POST" action="{{ route('payrolls.approve', $period) }}" class="mb-3">@csrf<button class="btn btn-success">Phê duyệt và công bố</button></form>
<form method="POST" action="{{ route('payrolls.reject', $period) }}">@csrf<label for="rejection_reason" class="form-label">Lý do từ chối</label><textarea name="rejection_reason" id="rejection_reason" class="form-control mb-2" required maxlength="5000">{{ old('rejection_reason') }}</textarea><button class="btn btn-outline-danger">Từ chối</button></form></div>@endif @endif
@endsection
