@extends('admin.layout.masterlayout')
@section('title', 'Kỳ lương')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4"><h1 class="h4 mb-0">Kỳ lương</h1></div>
@if(auth()->user()->canManageHrm())<a class="btn btn-primary mb-3" href="{{ route('payrolls.create') }}">Tạo kỳ lương</a>@endif
<div class="card table-responsive"><table class="table"><thead><tr><th>Kỳ lương</th><th>Công chuẩn</th><th>Số nhân viên</th><th>Trạng thái</th><th>Thao tác</th></tr></thead><tbody>@forelse($periods as $period)<tr><td>{{ $period->month }}/{{ $period->year }}</td><td>{{ $period->standard_work_days }}</td><td>{{ $period->details_count }}</td><td>@include('admin.partial.status', ['status'=>$period->status])</td><td><a class="btn btn-sm btn-outline-primary" href="{{ route('payrolls.show', $period) }}">Xem</a></td></tr>@empty<tr><td colspan="5">Chưa có kỳ lương.</td></tr>@endforelse</tbody></table></div><div class="mt-3">{{ $periods->links('pagination::bootstrap-5') }}</div>
@endsection
