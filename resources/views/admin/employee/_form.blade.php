@php($editingEmployee = $employee ?? null)

<div class="row g-3">
    @if ($editingEmployee)
        <div class="col-md-6">
            <label class="form-label">Mã nhân viên</label>
            <p class="form-control-plaintext mb-0">{{ $editingEmployee->employee_code }}</p>
            <div class="form-text">Mã được hệ thống quản lý và không thể thay đổi.</div>
        </div>
    @else
        <div class="col-md-6">
            <label class="form-label">Mã nhân viên</label>
            <p class="form-control-plaintext mb-0">Tự sinh sau khi lưu hồ sơ.</p>
            <div class="form-text">Theo thứ tự NV001–NV999 đang còn trống.</div>
        </div>
    @endif

    <div class="col-md-6">
        <label for="profile_photo" class="form-label">Ảnh đại diện</label>
        <input id="profile_photo" name="profile_photo" type="file"
            class="form-control @error('profile_photo') is-invalid @enderror"
            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
        <div class="form-text">JPG, JPEG, PNG hoặc WEBP; tối đa 2 MB.</div>
        @error('profile_photo')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        @if ($editingEmployee && $editingEmployee->profile_photo_path)
            <img class="rounded mt-2" src="{{ route('employees.photo', $editingEmployee) }}"
                alt="Ảnh đại diện {{ $editingEmployee->full_name }}" width="64" height="64" style="object-fit: cover">
        @endif
    </div>

    <div class="col-md-6">
        <label for="full_name" class="form-label">Họ tên <span class="required">*</span></label>
        <input id="full_name" name="full_name" type="text"
            class="form-control @error('full_name') is-invalid @enderror"
            value="{{ old('full_name', $editingEmployee?->full_name) }}" required maxlength="150" autocomplete="name">
        @error('full_name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
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

    <div class="col-md-6">
        <label for="date_of_birth" class="form-label">Ngày sinh</label>
        <input id="date_of_birth" name="date_of_birth" type="date"
            class="form-control @error('date_of_birth') is-invalid @enderror"
            value="{{ old('date_of_birth', $editingEmployee?->date_of_birth?->format('Y-m-d')) }}" max="{{ today()->toDateString() }}">
        @error('date_of_birth')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="phone" class="form-label">Điện thoại</label>
        <input id="phone" name="phone" type="tel"
            class="form-control @error('phone') is-invalid @enderror"
            value="{{ old('phone', $editingEmployee?->phone) }}" maxlength="20" inputmode="tel" autocomplete="tel">
        @error('phone')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="personal_email" class="form-label">Email cá nhân <span class="required">*</span></label>
        <input id="personal_email" name="personal_email" type="email"
            class="form-control @error('personal_email') is-invalid @enderror"
            value="{{ old('personal_email', $editingEmployee?->personal_email) }}" required maxlength="255" autocomplete="email">
        @error('personal_email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="work_email" class="form-label">Email công việc</label>
        <input id="work_email" name="work_email" type="email"
            class="form-control @error('work_email') is-invalid @enderror"
            value="{{ old('work_email', $editingEmployee?->work_email) }}" maxlength="255">
        @error('work_email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="address" class="form-label">Địa chỉ</label>
        <input id="address" name="address" type="text"
            class="form-control @error('address') is-invalid @enderror"
            value="{{ old('address', $editingEmployee?->address) }}" maxlength="255" autocomplete="street-address">
        @error('address')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="hire_date" class="form-label">Ngày nhận việc</label>
        <input id="hire_date" name="hire_date" type="date"
            class="form-control @error('hire_date') is-invalid @enderror"
            value="{{ old('hire_date', $editingEmployee?->hire_date?->format('Y-m-d')) }}">
        @error('hire_date')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="employment_status" class="form-label">Trạng thái nhân viên <span class="required">*</span></label>
        <select class="form-select @error('employment_status') is-invalid @enderror" name="employment_status" id="employment_status" required>
            @foreach (['pending' => 'Chờ nhận việc', 'working' => 'Đang làm việc', 'on_leave' => 'Nghỉ phép', 'resigned' => 'Nghỉ việc'] as $value => $label)
                <option value="{{ $value }}" @selected(old('employment_status', $editingEmployee?->employment_status ?? 'pending') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('employment_status')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="department_id" class="form-label">Phòng ban <span class="required">*</span></label>
        <select required name="department_id" id="department_id" class="form-select @error('department_id') is-invalid @enderror">
            <option value="">Chọn phòng ban</option>
            @foreach ($departments as $option)
                <option value="{{ $option->id }}" @selected((string) old('department_id', $editingEmployee?->department_id) === (string) $option->id)>
                    {{ $option->code }} — {{ $option->name }}
                </option>
            @endforeach
        </select>
        @error('department_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="position_id" class="form-label">Chức vụ <span class="required">*</span></label>
        <select required name="position_id" id="position_id" class="form-select @error('position_id') is-invalid @enderror">
            <option value="">Chọn chức vụ</option>
            @foreach ($positions as $option)
                <option value="{{ $option->id }}" @selected((string) old('position_id', $editingEmployee?->position_id) === (string) $option->id)>
                    {{ $option->code }} — {{ $option->name }}
                </option>
            @endforeach
        </select>
        @error('position_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
