@php
    $selectedEmp = old('employee_id', $contract->employee_id ?? ($selectedEmployeeId ?? ''));
    $selectedPrev = old('previous_contract_id', $contract->previous_contract_id ?? ($suggestedPreviousContractId ?? ''));
@endphp

@if($errors->any())
<div class="alert alert-danger mb-3">
    <div class="fw-semibold mb-1">Vui lòng kiểm tra lại dữ liệu hợp đồng:</div>
    <ul class="mb-0 ps-3">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label" for="contract_code">Mã hợp đồng</label>
        <input type="text" class="form-control @error('contract_code') is-invalid @enderror"
               id="contract_code" name="contract_code" maxlength="50"
               placeholder="Để trống tự sinh (VD: HD008)"
               value="{{ old('contract_code', $contract->contract_code ?? '') }}">
        @error('contract_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <label class="form-label" for="employee_id">Nhân viên (Đã duyệt hồ sơ) <span class="text-danger">*</span></label>
        <select class="form-select @error('employee_id') is-invalid @enderror" id="employee_id" name="employee_id" required @isset($contract) disabled @endisset>
            <option value="">-- Chọn nhân viên --</option>
            @foreach($employees as $emp)
                <option value="{{ $emp->id }}" @selected((string) $selectedEmp === (string) $emp->id)>
                    {{ $emp->employee_code }} — {{ $emp->full_name }}
                </option>
            @endforeach
        </select>
        @isset($contract)
            <input type="hidden" name="employee_id" value="{{ $contract->employee_id }}">
        @endisset
        @error('employee_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <label class="form-label" for="previous_contract_id">Hợp đồng liền trước (Chuỗi gia hạn)</label>
        <select class="form-select @error('previous_contract_id') is-invalid @enderror" id="previous_contract_id" name="previous_contract_id">
            <option value="">-- Không có (Hợp đồng đầu tiên) --</option>
            @foreach($previousContracts as $prev)
                <option value="{{ $prev->id }}"
                        data-employee-id="{{ $prev->employee_id }}"
                        @selected((string) $selectedPrev === (string) $prev->id)>
                    {{ $prev->contract_code }} — {{ $prev->employee?->full_name }} ({{ $prev->effective_from?->format('d/m/Y') }})
                </option>
            @endforeach
        </select>
        <div class="form-text">Tự động gợi ý hợp đồng gần nhất của nhân viên khi gia hạn.</div>
        @error('previous_contract_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-3">
        <label class="form-label" for="contract_type">Loại hợp đồng <span class="text-danger">*</span></label>
        <select class="form-select @error('contract_type') is-invalid @enderror" id="contract_type" name="contract_type" required>
            @foreach(['probation' => 'Thử việc', 'fixed_term' => 'Có thời hạn', 'indefinite' => 'Không xác định thời hạn'] as $value => $label)
                <option value="{{ $value }}" @selected(old('contract_type', $contract->contract_type ?? 'fixed_term') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('contract_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-3">
        <label class="form-label" for="signed_date">Ngày ký hợp đồng</label>
        <input type="date" class="form-control @error('signed_date') is-invalid @enderror"
               id="signed_date" name="signed_date"
               value="{{ old('signed_date', isset($contract) ? $contract->signed_date?->format('Y-m-d') : now()->format('Y-m-d')) }}">
        @error('signed_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-3">
        <label class="form-label" for="effective_from">Ngày có hiệu lực <span class="text-danger">*</span></label>
        <input type="date" class="form-control @error('effective_from') is-invalid @enderror"
               id="effective_from" name="effective_from"
               value="{{ old('effective_from', isset($contract) ? $contract->effective_from?->format('Y-m-d') : now()->format('Y-m-d')) }}" required>
        @error('effective_from')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-3">
        <label class="form-label" for="effective_to">Ngày hết hạn</label>
        <input type="date" class="form-control @error('effective_to') is-invalid @enderror"
               id="effective_to" name="effective_to"
               value="{{ old('effective_to', isset($contract) ? $contract->effective_to?->format('Y-m-d') : '') }}">
        <div class="form-text">Để trống nếu là HĐ không xác định thời hạn.</div>
        @error('effective_to')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <label class="form-label" for="base_salary">Lương cơ bản (VNĐ) <span class="text-danger">*</span></label>
        <input class="form-control @error('base_salary') is-invalid @enderror" type="number"
               id="base_salary" name="base_salary" min="0" step="0.01"
               value="{{ old('base_salary', $contract->base_salary ?? '') }}" required>
        @error('base_salary')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <label class="form-label" for="coefficient">Hệ số lương <span class="text-danger">*</span></label>
        <input class="form-control @error('coefficient') is-invalid @enderror" type="number"
               id="coefficient" name="coefficient" min="0.01" step="0.01"
               value="{{ old('coefficient', $contract->coefficient ?? '1.00') }}" required>
        @error('coefficient')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <label class="form-label" for="allowance">Tổng phụ cấp (VNĐ) <span class="text-danger">*</span></label>
        <input class="form-control @error('allowance') is-invalid @enderror" type="number"
               id="allowance" name="allowance" min="0" step="0.01"
               value="{{ old('allowance', $contract->allowance ?? '0') }}" required>
        @error('allowance')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label" for="allowance_note">Ghi chú phụ cấp (Chi tiết các khoản phụ cấp)</label>
        <textarea class="form-control @error('allowance_note') is-invalid @enderror"
                  id="allowance_note" name="allowance_note" rows="3"
                  placeholder="Mô tả chi tiết các khoản phụ cấp (VD: Phụ cấp ăn trưa 800.000đ, xăng xe 500.000đ, điện thoại 300.000đ)...">{{ old('allowance_note', $contract->allowance_note ?? '') }}</textarea>
        @error('allowance_note')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label" for="pdf_file">
            File PDF hợp đồng đã ký giấy
            @if(!isset($contract) && auth()->user()->isDirector())
                <span class="text-danger">* (Bắt buộc khi Giám đốc tạo trực tiếp)</span>
            @endif
        </label>
        <input type="file" class="form-control @error('pdf_file') is-invalid @enderror"
               id="pdf_file" name="pdf_file" accept=".pdf,application/pdf"
               @if(!isset($contract) && auth()->user()->isDirector()) required @endif>
        <div class="form-text">
            Chỉ chấp nhận định dạng PDF (tối đa 10MB). File được mã hóa tên UUID và lưu bảo mật tại <code>storage/app/private/contracts/</code>.
            @if(isset($contract) && $contract->file_path)
                <br><span class="text-success fw-semibold">Đã có file PDF đính kèm.</span> Chọn file mới nếu muốn thay thế.
            @endif
        </div>
        @error('pdf_file')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

@if(!isset($contract))
<script>
document.addEventListener('DOMContentLoaded', function () {
    const latestMap = @json($latestContractByEmployee ?? []);
    const empSelect = document.getElementById('employee_id');
    const prevSelect = document.getElementById('previous_contract_id');

    if (empSelect && prevSelect) {
        empSelect.addEventListener('change', function () {
            const empId = this.value;
            const suggestedId = latestMap[empId] ? String(latestMap[empId]) : '';
            prevSelect.value = suggestedId;
        });
    }
});
</script>
@endif
