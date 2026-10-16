@extends('admin.layout.masterlayout')
@section('title', 'Sửa nhân viên')
@section('styles')
<style>
    /* Kế thừa style tương tự create */
    .form-card {
        background: #fff;
        border: 1px solid var(--border-color);
        border-radius: var(--radius);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
    }

    .form-card-header {
        padding: 18px 24px;
        border-bottom: 1px solid var(--border-color);
        background: #fafbfc;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .form-card-header h5 {
        margin: 0;
        font-size: .95rem;
        font-weight: 700;
        color: #1e293b;
    }

    .form-card-body { padding: 28px 28px 24px 28px; }

    .form-label {
        font-size: .82rem;
        font-weight: 600;
        color: #475569;
        margin-bottom: 5px;
    }

    .form-label .required { color: #ef4444; margin-left: 2px; }

    .form-control, .form-select {
        border-radius: 9px;
        border-color: #e2e8f0;
        font-size: .875rem;
        padding: 9px 13px;
        transition: border-color .2s, box-shadow .2s;
    }

    .form-control:focus, .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37,99,235,.1);
    }

    .form-control.is-invalid, .form-select.is-invalid { border-color: #ef4444; }

    .form-footer {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
        padding: 18px 28px;
        border-top: 1px solid var(--border-color);
        background: #fafbfc;
    }

    /* Badge trạng thái ở tiêu đề */
    .page-status-badge {
        font-size: .75rem;
        font-weight: 600;
        padding: 4px 12px;
        border-radius: 20px;
    }
</style>
@endsection
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4"><h1 class="h4 mb-0">Sửa nhân viên</h1></div>
<div class="form-card"><form method="POST" action="{{ route('employees.update', $employee) }}">@csrf@method('PUT')<div class="form-card-body p-4">@include('admin.employee._form')</div><div class="form-footer p-3"><a href="{{ route('employees.index') }}" class="btn btn-light">Quay lại</a><button class="btn btn-primary">Lưu hồ sơ</button></div></form></div>
@endsection
