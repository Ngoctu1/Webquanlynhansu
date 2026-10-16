@extends('admin.layout.masterlayout')
@section('title', 'Thêm phòng ban')
@section('styles')
<style>
    .catalog-card { background:#fff; border:1px solid var(--border-color); border-radius:var(--radius); box-shadow:var(--shadow-sm); }
    .catalog-table th { background:#f8fafc; color:#64748b; font-size:.8rem; white-space:nowrap; }
    .catalog-table td, .catalog-table th { padding:14px; vertical-align:middle; }
    .catalog-description { white-space:pre-wrap; overflow-wrap:anywhere; }
</style>
@endsection
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4"><h1 class="h4 mb-0">Thêm phòng ban</h1></div>
<div class="card p-4"><form method="POST" action="{{ route('departments.store') }}">@csrf @include('admin.department._form')<a href="{{ route('departments.index') }}" class="btn btn-light">Quay lại</a> <button class="btn btn-primary">Lưu</button></form></div>
@endsection
