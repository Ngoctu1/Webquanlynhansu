@extends('admin.layout.masterlayout')
@section('title', 'Bổ sung chấm công')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4"><h1 class="h4 mb-0">Bổ sung chấm công</h1></div>
<div class="card p-4"><form method="POST" action="{{ route('attendances.store') }}">@csrf @include('admin.attendance._form')<button class="btn btn-primary">Xác nhận công</button> <a href="{{ route('attendances.index') }}" class="btn btn-light">Quay lại</a></form></div>
@endsection
