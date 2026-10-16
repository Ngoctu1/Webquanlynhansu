@extends('admin.layout.masterlayout')
@section('title', 'Lịch làm việc')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4"><h1 class="h4 mb-0">Lịch làm việc</h1></div>
@if(auth()->user()->canManageHrm())<a class="btn btn-primary mb-3" href="{{ route('schedules.create') }}">Thêm lịch</a>@endif<div class="card table-responsive"><table class="table"><thead><tr><th>Tên lịch</th><th>Ca / nghỉ giữa ca</th><th>Phút chuẩn</th><th>Cho phép muộn</th><th>Ngày làm việc ISO (1=T2)</th><th>Nhân viên</th><th>Trạng thái</th><th></th></tr></thead><tbody>
@foreach($schedules as $schedule)<tr><td>{{ $schedule->name }}</td><td>{{ $schedule->start_time }}–{{ $schedule->end_time }}<br>{{ $schedule->break_start }}–{{ $schedule->break_end }}</td><td>{{ $schedule->standard_minutes }}</td><td>{{ $schedule->late_grace_minutes }} phút</td><td>{{ implode(', ', $schedule->work_days) }}</td><td>{{ $schedule->employees_count }}</td><td>@include('admin.partial.status', ['status'=>$schedule->status])</td><td>@if(auth()->user()->canManageHrm())<a href="{{ route('schedules.edit', $schedule) }}" class="btn btn-sm btn-outline-primary">Sửa</a>@endif</td></tr>@endforeach</tbody></table></div>
@endsection
