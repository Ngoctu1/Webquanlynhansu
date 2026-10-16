@extends('admin.layout.masterlayout')
@section('title', 'Tạo hợp đồng')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4"><h1 class="h4 mb-0">Tạo hợp đồng</h1></div>
<div class="card p-4"><form method="POST" action="{{ route('contracts.store') }}">@csrf @include('admin.contract._form')<button class="btn btn-primary">Lưu bản nháp</button> <a href="{{ route('contracts.index') }}" class="btn btn-light">Quay lại</a></form></div>
@endsection
