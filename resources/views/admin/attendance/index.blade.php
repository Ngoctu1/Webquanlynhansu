@extends('admin.layout.masterlayout')
@section('title', 'Quản lý chấm công')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4"><h1 class="h4 mb-0">Quản lý chấm công</h1></div>
@if(auth()->user()->canManageHrm())<a class="btn btn-primary mb-3" href="{{ route('attendances.create') }}">Bổ sung chấm công</a>@endif
<form method="GET" class="row g-2 mb-3"><div class="col-md-4"><select name="employee_id" class="form-select" aria-label="Nhân viên"><option value="">Tất cả nhân viên</option>@foreach($employees as $employee)<option value="{{ $employee->id }}" @selected((string)request('employee_id')===(string)$employee->id)>{{ $employee->employee_code }} — {{ $employee->full_name }}</option>@endforeach</select></div><div class="col-md-3"><input type="date" class="form-control" name="from" value="{{ request('from') }}" aria-label="Từ ngày"></div><div class="col-md-3"><input type="date" class="form-control" name="to" value="{{ request('to') }}" aria-label="Đến ngày"></div><div class="col-md-2"><button class="btn btn-outline-primary">Lọc</button></div></form>
@if($missingDates)<div class="alert alert-warning"><strong>Chờ HR xử lý — chưa có chấm công:</strong> {{ implode(', ', $missingDates) }}. Xác nhận ngày lễ/nghỉ phép/vắng mặt bằng điều chỉnh có lý do; không tự tính 0 công.</div>@else<p class="text-muted">Chọn nhân viên và khoảng ngày để kiểm tra ngày thiếu chấm công (tối đa 62 ngày).</p>@endif
@php
    $incompleteCount = $attendances->filter(fn ($item) => $item->status === 'checked_in' && $item->check_in && !$item->check_out && $item->work_date->lt(today()))->count();
@endphp
@if($incompleteCount)
    <div class="alert alert-warning" role="alert">
        <strong>{{ $incompleteCount }} bản ghi trên trang này đã qua ngày nhưng chưa check-out.</strong>
        Sử dụng chức năng Điều chỉnh để xác nhận giờ thực tế hoặc quyết định công hưởng lương, bắt buộc ghi lý do. Các bản ghi này chưa được xem là 0 công hoặc đủ công.
    </div>
@endif
<div class="card table-responsive"><table class="table align-middle"><thead><tr><th>Ngày</th><th>Nhân viên</th><th>Vào / ra</th><th>Muộn</th><th>Công</th><th>Tăng ca</th><th>Trạng thái / lý do</th><th>Thao tác</th></tr></thead><tbody>
@forelse($attendances as $row)
@php
    $rowIncomplete = $row->status === 'checked_in' && $row->check_in && !$row->check_out;
    $rowMissingCheckout = $rowIncomplete && $row->work_date->lt(today());
@endphp
<tr class="{{ $rowMissingCheckout ? 'table-warning' : '' }}">
    <td>{{ $row->work_date->format('d/m/Y') }}</td>
    <td>{{ $row->employee->full_name }}</td>
    <td>{{ $row->check_in?->format('d/m H:i') ?? '—' }}<br>{{ $row->check_out?->format('d/m H:i') ?? '—' }}</td>
    <td>{{ $row->late_minutes }} phút</td>
    <td>
        @if($rowIncomplete)
            <span class="badge text-bg-warning">Chờ xác nhận</span>
        @else
            {{ $row->work_units }}
        @endif
    </td>
    <td>
        @if($rowIncomplete)
            Chưa xác nhận
        @else
            {{ $row->overtime_hours }} giờ
        @endif
    </td>
    <td>
        @include('admin.partial.status', ['status'=>$row->status])
        @if($rowMissingCheckout)<div class="small fw-semibold text-danger">Quên check-out · cần HR xử lý</div>@endif
        <div class="small">{{ $row->adjustment_reason }}</div>
    </td>
    <td>
        @if(auth()->user()->canManageHrm())
            <a href="{{ route('attendances.edit', $row) }}" class="btn btn-sm btn-outline-primary">Điều chỉnh</a>
        @endif
    </td>
</tr>
@empty<tr><td colspan="8">Chưa có chấm công.</td></tr>@endforelse</tbody></table></div><div class="mt-3">{{ $attendances->links('pagination::bootstrap-5') }}</div>
@endsection
