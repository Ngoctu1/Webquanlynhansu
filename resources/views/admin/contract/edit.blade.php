@extends('admin.layout.masterlayout')
@section('title', 'Sửa hợp đồng')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4"><h1 class="h4 mb-0">Sửa hợp đồng</h1></div>
<div class="card p-4"><form method="POST" action="{{ route('contracts.update', $contract) }}">@csrf @method('PUT')@include('admin.contract._form')<button class="btn btn-primary">Lưu bản nháp</button> <a href="{{ route('contracts.index') }}" class="btn btn-light">Quay lại</a></form></div>
@endsection
