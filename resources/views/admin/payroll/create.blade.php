@extends('admin.layout.masterlayout')
@section('title', 'Tạo kỳ lương')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4"><h1 class="h4 mb-0">Tạo kỳ lương</h1></div>
<div class="card p-4"><form method="POST" action="{{ route('payrolls.store') }}">@csrf<div class="row">
<div class="col-md-4 mb-3"><label class="form-label" for="month">Tháng</label><input class="form-control" type="number" name="month" id="month" min="1" max="12" step="1" value="{{ old('month', now()->month) }}" required></div><div class="col-md-4 mb-3"><label class="form-label" for="year">Năm</label><input class="form-control" type="number" name="year" id="year" min="2000" max="2100" step="1" value="{{ old('year', now()->year) }}" required></div><div class="col-md-4 mb-3"><label class="form-label" for="work_schedule_id">Lịch tính công chuẩn tháng</label><select name="work_schedule_id" id="work_schedule_id" class="form-select" required>@foreach($schedules as $schedule)<option value="{{ $schedule->id }}" @selected((string)old('work_schedule_id')===(string)$schedule->id)>{{ $schedule->name }}</option>@endforeach</select></div>
</div><button class="btn btn-primary">Tự tính công chuẩn và tạo kỳ lương</button></form></div>
@endsection
