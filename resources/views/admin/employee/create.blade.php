{{-- ================================================================
     THÊM NHÂN VIÊN
     resources/views/admin/employee/create.blade.php
     ================================================================ --}}
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

{{-- ===== TIÊU ĐỀ TRANG ===== --}}
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
    <div>
        <h1 class="mb-0" style="font-size:1.5rem;font-weight:700;color:#1e293b;">
            <i class="bi bi-person-plus-fill text-primary me-2"></i> Thêm nhân viên
        </h1>
        <p class="text-secondary mb-0 mt-1" style="font-size:.875rem;">
            Điền đầy đủ thông tin để thêm nhân viên mới vào hệ thống
        </p>
    </div>
    <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2"
       style="border-radius:10px;font-weight:600;font-size:.875rem;">
        <i class="bi bi-arrow-left"></i> Quay lại danh sách
    </a>
</div>

{{-- ===== LỖI VALIDATION ===== --}}
@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show mb-4"
     style="border-radius:10px;border:none;background:#fee2e2;color:#991b1b;">
    <div class="d-flex align-items-start gap-2">
        <i class="bi bi-exclamation-triangle-fill mt-1"></i>
        <div>
            <strong>Có {{ $errors->count() }} lỗi cần sửa:</strong>
            <ul class="mb-0 mt-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- ===== FORM ===== --}}
<form action="{{ route('employees.store') }}" method="POST" id="form-create-employee" novalidate>
    @csrf

    <div class="row g-4">

        {{-- ---- CỘT TRÁI: Thông tin cá nhân ---- --}}
        <div class="col-12 col-xl-8">

            {{-- Thông tin cơ bản --}}
            <div class="form-card mb-4">
                <div class="form-card-header">
                    <i class="bi bi-person text-primary"></i>
                    <h5>Thông tin cơ bản</h5>
                </div>
                <div class="form-card-body">

                    <div class="row g-3">

                        {{-- Mã nhân viên --}}
                        <div class="col-12 col-md-4">
                            <label for="ma_nhan_vien" class="form-label">
                                Mã nhân viên <span class="required">*</span>
                            </label>
                            <input type="text" id="ma_nhan_vien" name="employee_code"
                                   class="form-control @error('employee_code') is-invalid @enderror"
                                   value="{{ old('employee_code') }}"
                                   placeholder="VD: NV-001"
                                   maxlength="20">
                            @error('employee_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Họ và tên --}}
                        <div class="col-12 col-md-8">
                            <label for="ho_ten" class="form-label">
                                Họ và tên <span class="required">*</span>
                            </label>
                            <input type="text" id="ho_ten" name="full_name"
                                   class="form-control @error('full_name') is-invalid @enderror"
                                   value="{{ old('full_name') }}"
                                   placeholder="Nhập họ và tên đầy đủ"
                                   maxlength="100">
                            @error('full_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Ngày sinh --}}
                        <div class="col-12 col-md-4">
                            <label for="ngay_sinh" class="form-label">Ngày sinh</label>
                            <input type="date" id="ngay_sinh" name="date_of_birth"
                                   class="form-control @error('date_of_birth') is-invalid @enderror"
                                   value="{{ old('date_of_birth') }}">
                            @error('date_of_birth')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Giới tính --}}
                        <div class="col-12 col-md-4">
                            <label for="gioi_tinh" class="form-label">Giới tính</label>
                            <select id="gioi_tinh" name="gender"
                                    class="form-select @error('gender') is-invalid @enderror">
                                <option value="">-- Chọn giới tính --</option>
                                <option value="Nam"  {{ old('gender') == 'Nam'  ? 'selected' : '' }}>Nam</option>
                                <option value="Nữ"   {{ old('gender') == 'Nữ'   ? 'selected' : '' }}>Nữ</option>
                                <option value="Khác" {{ old('gender') == 'Khác' ? 'selected' : '' }}>Khác</option>
                            </select>
                            @error('gender')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- CCCD --}}
                        <div class="col-12 col-md-4">
                            <label for="cccd" class="form-label">Số CCCD / CMND</label>
                            <input type="text" id="cccd" name="cccd"
                                   class="form-control @error('cccd') is-invalid @enderror"
                                   value="{{ old('cccd') }}"
                                   placeholder="VD: 012345678901"
                                   maxlength="20">
                            @error('cccd')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Số điện thoại --}}
                        <div class="col-12 col-md-4">
                            <label for="so_dien_thoai" class="form-label">Số điện thoại</label>
                            <input type="text" id="so_dien_thoai" name="phone"
                                   class="form-control @error('phone') is-invalid @enderror"
                                   value="{{ old('phone') }}"
                                   placeholder="VD: 0901234567"
                                   maxlength="15">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div class="col-12 col-md-8">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" id="email" name="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}"
                                   placeholder="VD: nhanvien@company.vn"
                                   maxlength="100">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Địa chỉ --}}
                        <div class="col-12">
                            <label for="dia_chi" class="form-label">Địa chỉ</label>
                            <textarea id="dia_chi" name="address" rows="2"
                                      class="form-control @error('address') is-invalid @enderror"
                                      placeholder="Nhập địa chỉ thường trú..."
                                      maxlength="255">{{ old('address') }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>
                </div>
            </div>

        </div>

        {{-- ---- CỘT PHẢI: Thông tin công việc ---- --}}
        <div class="col-12 col-xl-4">

            {{-- Thông tin công việc --}}
            <div class="form-card">
                <div class="form-card-header">
                    <i class="bi bi-briefcase text-primary"></i>
                    <h5>Thông tin công việc</h5>
                </div>
                <div class="form-card-body">
                    <div class="row g-3">

                        {{-- Ngày vào làm --}}
                        <div class="col-12">
                            <label for="ngay_vao_lam" class="form-label">Ngày vào làm</label>
                            <input type="date" id="ngay_vao_lam" name="hire_date"
                                   class="form-control @error('hire_date') is-invalid @enderror"
                                   value="{{ old('hire_date') }}">
                            @error('hire_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Phòng ban --}}
                        <div class="col-12">
                            <label for="department_id" class="form-label">Phòng ban</label>
                            <select id="department_id" name="department_id"
                                    class="form-select @error('department_id') is-invalid @enderror">
                                <option value="">-- Chọn phòng ban --</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}"
                                        {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                        {{ $dept->ten_phong_ban ?? $dept->name ?? 'Phòng ban ' . $dept->id }}
                                    </option>
                                @endforeach
                            </select>
                            @error('department_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Chức vụ --}}
                        <div class="col-12">
                            <label for="position_id" class="form-label">Chức vụ</label>
                            <select id="position_id" name="position_id"
                                    class="form-select @error('position_id') is-invalid @enderror">
                                <option value="">-- Chọn chức vụ --</option>
                                @foreach($positions as $pos)
                                    <option value="{{ $pos->id }}"
                                        {{ old('position_id') == $pos->id ? 'selected' : '' }}>
                                        {{ $pos->ten_chuc_vu ?? $pos->name ?? 'Chức vụ ' . $pos->id }}
                                    </option>
                                @endforeach
                            </select>
                            @error('position_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Trạng thái --}}
                        <div class="col-12">
                            <label for="trang_thai" class="form-label">
                                Trạng thái <span class="required">*</span>
                            </label>
                            <select id="trang_thai" name="status"
                                    class="form-select @error('status') is-invalid @enderror">
                                <option value="">-- Chọn trạng thái --</option>
                                <option value="Thử việc"      {{ old('status', 'Thử việc') == 'Thử việc'      ? 'selected' : '' }}>Thử việc</option>
                                <option value="Đang làm việc" {{ old('status') == 'Đang làm việc' ? 'selected' : '' }}>Đang làm việc</option>
                                <option value="Nghỉ phép"     {{ old('status') == 'Nghỉ phép'     ? 'selected' : '' }}>Nghỉ phép</option>
                                <option value="Đã nghỉ việc"  {{ old('status') == 'Đã nghỉ việc'  ? 'selected' : '' }}>Đã nghỉ việc</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>
                </div>
            </div>

        </div>

    </div>{{-- /row --}}

    {{-- ===== FOOTER BUTTONS ===== --}}
    <div class="form-footer mt-4 rounded-3" style="border:1px solid var(--border-color);">
        <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary"
           style="border-radius:9px;font-weight:600;">
            <i class="bi bi-x-lg me-1"></i> Hủy
        </a>
        <button type="submit" class="btn btn-primary"
                style="border-radius:9px;font-weight:600;padding:9px 24px;">
            <i class="bi bi-check-lg me-1"></i> Lưu nhân viên
        </button>
    </div>

</form>

@endsection

@section('scripts')
<script>
    // Auto-generate mã nhân viên gợi ý (chỉ gợi ý, không bắt buộc)
    document.addEventListener('DOMContentLoaded', function () {
        const maInput = document.getElementById('ma_nhan_vien');
        if (maInput && !maInput.value) {
            const ts = Date.now().toString().slice(-4);
            maInput.placeholder = 'VD: NV-' + ts;
        }
    });
</script>
@endsection
