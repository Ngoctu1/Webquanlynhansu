@php($editingEmployee = $employee ?? null)

<div class="row g-3">
    @if ($editingEmployee)
        <div class="col-md-6">
            <label class="form-label">Mã nhân viên</label>
            <input type="text" class="form-control" value="{{ $editingEmployee->employee_code }}" disabled>
            <div class="form-text">Mã nhân viên không thể thay đổi sau khi tạo.</div>
        </div>
    @else
        <div class="col-md-6">
            <label for="employee_code" class="form-label">Mã nhân viên (để trống để tự sinh NV00x)</label>
            <input id="employee_code" name="employee_code" type="text"
                class="form-control @error('employee_code') is-invalid @enderror"
                value="{{ old('employee_code') }}" maxlength="20" placeholder="VD: NV011">
            @error('employee_code')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    @endif

    <div class="col-md-6">
        <label for="avatar" class="form-label">Ảnh đại diện</label>
        <input id="avatar" name="avatar" type="file"
            class="form-control @error('avatar') is-invalid @enderror"
            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
        <div class="form-text">JPG, JPEG, PNG hoặc WEBP; tối đa 2 MB.</div>
        @error('avatar')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        @if ($editingEmployee && $editingEmployee->avatar)
            <img class="rounded mt-2" src="{{ route('employees.photo', $editingEmployee) }}"
                alt="Ảnh đại diện {{ $editingEmployee->full_name }}" width="64" height="64" style="object-fit: cover">
        @endif
    </div>

    <div class="col-md-6">
        <label for="full_name" class="form-label">Họ tên <span class="required">*</span></label>
        <input id="full_name" name="full_name" type="text"
            class="form-control @error('full_name') is-invalid @enderror"
            value="{{ old('full_name', $editingEmployee?->full_name) }}" required maxlength="150">
        @error('full_name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="citizen_id" class="form-label">Số CCCD (12 chữ số) <span class="required">*</span></label>
        <input id="citizen_id" name="citizen_id" type="text"
            class="form-control @error('citizen_id') is-invalid @enderror"
            value="{{ old('citizen_id', $editingEmployee?->citizen_id) }}" required pattern="\d{12}" maxlength="12" placeholder="Nhập đúng 12 chữ số">
        @error('citizen_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="gender" class="form-label">Giới tính</label>
        <select class="form-select @error('gender') is-invalid @enderror" name="gender" id="gender">
            <option value="">Chưa cung cấp</option>
            @foreach (['male' => 'Nam', 'female' => 'Nữ', 'other' => 'Khác'] as $value => $label)
                <option value="{{ $value }}" @selected(old('gender', $editingEmployee?->gender) === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('gender')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="date_of_birth" class="form-label">Ngày sinh (&gt;= 18 tuổi tại ngày nhận việc) <span class="required">*</span></label>
        <input id="date_of_birth" name="date_of_birth" type="date"
            class="form-control @error('date_of_birth') is-invalid @enderror"
            value="{{ old('date_of_birth', $editingEmployee?->date_of_birth?->format('Y-m-d')) }}" required max="{{ today()->toDateString() }}">
        @error('date_of_birth')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="phone" class="form-label">Số điện thoại</label>
        <input id="phone" name="phone" type="tel"
            class="form-control @error('phone') is-invalid @enderror"
            value="{{ old('phone', $editingEmployee?->phone) }}" maxlength="20">
        @error('phone')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="work_email" class="form-label">Email công ty (Dùng để đăng nhập) <span class="required">*</span></label>
        <input id="work_email" name="work_email" type="email"
            class="form-control @error('work_email') is-invalid @enderror"
            value="{{ old('work_email', $editingEmployee?->work_email) }}" required maxlength="150">
        @error('work_email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="personal_email" class="form-label">Email cá nhân</label>
        <input id="personal_email" name="personal_email" type="email"
            class="form-control @error('personal_email') is-invalid @enderror"
            value="{{ old('personal_email', $editingEmployee?->personal_email) }}" maxlength="150">
        @error('personal_email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-12">
        <label for="address" class="form-label">Địa chỉ thường trú</label>
        <input id="address" name="address" type="text"
            class="form-control @error('address') is-invalid @enderror"
            value="{{ old('address', $editingEmployee?->address) }}" maxlength="255">
        @error('address')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="department_id" class="form-label">Phòng ban <span class="required">*</span></label>
        <select required name="department_id" id="department_id" class="form-select @error('department_id') is-invalid @enderror">
            <option value="">Chọn phòng ban</option>
            @foreach ($departments as $option)
                <option value="{{ $option->id }}" @selected((string) old('department_id', $editingEmployee?->department_id) === (string) $option->id)>
                    {{ $option->name }}
                </option>
            @endforeach
        </select>
        @error('department_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="position_id" class="form-label">Chức vụ <span class="required">*</span></label>
        <select required name="position_id" id="position_id" class="form-select @error('position_id') is-invalid @enderror">
            <option value="">Chọn chức vụ</option>
            @foreach ($positions as $option)
                <option value="{{ $option->id }}" @selected((string) old('position_id', $editingEmployee?->position_id) === (string) $option->id)>
                    {{ $option->name }}
                </option>
            @endforeach
        </select>
        @error('position_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="hire_date" class="form-label">Ngày nhận việc <span class="required">*</span></label>
        <input id="hire_date" name="hire_date" type="date"
            class="form-control @error('hire_date') is-invalid @enderror"
            value="{{ old('hire_date', $editingEmployee?->hire_date?->format('Y-m-d') ?? today()->toDateString()) }}" required>
        @error('hire_date')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="annual_leave_days" class="form-label">Số ngày phép hưởng trong năm</label>
        <input id="annual_leave_days" name="annual_leave_days" type="number" min="0" max="255"
            class="form-control @error('annual_leave_days') is-invalid @enderror"
            value="{{ old('annual_leave_days', $editingEmployee?->annual_leave_days ?? 12) }}">
        @error('annual_leave_days')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    @if ($editingEmployee)
        <div class="col-md-6">
            <label for="employment_status" class="form-label">Trạng thái làm việc</label>
            <select class="form-select @error('employment_status') is-invalid @enderror" name="employment_status" id="employment_status">
                @foreach (['pending' => 'Chờ nhận việc', 'working' => 'Đang làm việc', 'on_leave' => 'Đang nghỉ phép', 'resigned' => 'Đã nghỉ việc'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('employment_status', $editingEmployee->employment_status) === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('employment_status')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    @else
        <input type="hidden" name="employment_status" value="pending">
    @endif
</div>
