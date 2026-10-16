@extends('admin.layout.masterlayout')
@section('title', 'Chi tiết phòng ban')
@section('styles')
<style>
    .catalog-card { background:#fff; border:1px solid var(--border-color); border-radius:var(--radius); box-shadow:var(--shadow-sm); }
    .catalog-table th { background:#f8fafc; color:#64748b; font-size:.8rem; white-space:nowrap; }
    .catalog-table td, .catalog-table th { padding:14px; vertical-align:middle; }
    .catalog-description { white-space:pre-wrap; overflow-wrap:anywhere; }
</style>
@endsection
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4"><h1 class="h4 mb-0">Chi tiết phòng ban</h1></div>
<div class="card p-4"><h2 class="h5">{{ $department->code }} — {{ $department->name }}</h2><p style="white-space:pre-wrap">{{ $department->description }}</p><p>Nhân viên tham chiếu: {{ $department->employees_count }}</p><p>@include('admin.partial.status', ['status' => $department->status])</p>
<div>@if(auth()->user()->canManageHrm())<a class="btn btn-primary" href="{{ route('departments.edit', $department) }}">Sửa</a>@endif <a class="btn btn-light" href="{{ route('departments.index') }}">Quay lại</a></div></div>
@endsection
