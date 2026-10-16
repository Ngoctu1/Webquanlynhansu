@extends('admin.layout.masterlayout')
@section('title', 'Điều chỉnh chấm công')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4"><h1 class="h4 mb-0">Điều chỉnh chấm công</h1></div>
@if($attendance->status === 'checked_in' && $attendance->check_in && !$attendance->check_out && $attendance->work_date->lt(today()))
    <div class="alert alert-warning" role="alert">Nhân viên chưa check-out ngày {{ $attendance->work_date->format('d/m/Y') }}. Hãy xác nhận giờ thực tế hoặc quyết định công hưởng lương và ghi rõ lý do. Giờ check-in đã lưu không tự bị thay đổi khi qua ngày.</div>
@endif
<div class="card p-4"><form method="POST" action="{{ route('attendances.update', $attendance) }}">@csrf @method('PUT')@include('admin.attendance._form')<button class="btn btn-primary">Xác nhận công</button> <a href="{{ route('attendances.index') }}" class="btn btn-light">Quay lại</a></form></div>
@endsection
