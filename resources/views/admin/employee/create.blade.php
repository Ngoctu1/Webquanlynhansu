@extends('admin.layout.masterlayout')
@section('title', 'Thêm nhân viên')
@section('styles')
<style>
    /* ---- Form Card ---- */
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

    .form-card-body {
        padding: 28px 28px 24px 28px;
    }

    /* ---- Form labels ---- */
    .form-label {
        font-size: .82rem;
        font-weight: 600;
        color: #475569;
        margin-bottom: 5px;
    }

    .form-label .required {
        color: #ef4444;
        margin-left: 2px;
    }

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

    .form-control.is-invalid, .form-select.is-invalid {
        border-color: #ef4444;
    }

    /* Section divider */
    .form-section-title {
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #64748b;
        padding-bottom: 8px;
        border-bottom: 2px solid #e2e8f0;
        margin-bottom: 18px;
    }

    /* Footer buttons */
    .form-footer {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
        padding: 18px 28px;
        border-top: 1px solid var(--border-color);
        background: #fafbfc;
    }
</style>
@endsection
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4"><h1 class="h4 mb-0">Thêm nhân viên</h1></div>
<div class="form-card"><form method="POST" action="{{ route('employees.store') }}">@csrf<div class="form-card-body p-4">@include('admin.employee._form')</div><div class="form-footer p-3"><a href="{{ route('employees.index') }}" class="btn btn-light">Quay lại</a><button class="btn btn-primary">Lưu hồ sơ</button></div></form></div>
@endsection
