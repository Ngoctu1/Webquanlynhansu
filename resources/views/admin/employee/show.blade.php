@extends('admin.layout.masterlayout')
@section('title', 'Chi tiết hồ sơ nhân viên')

@section('content')
@php
    $docLabels = [
        'cccd' => 'CCCD / Định danh',
        'cv' => 'Sơ yếu lý lịch / CV',
        'degree' => 'Bằng cấp / Chứng chỉ',
        'other' => 'Tài liệu khác',
    ];
    $contractTypes = [
        'probation' => 'Thử việc',
        'fixed_term' => 'Có thời hạn',
        'indefinite' => 'Không thời hạn',
    ];
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h1 class="h4 mb-1">{{ $employee->full_name }} <span class="text-primary">({{ $employee->employee_code }})</span></h1>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            @include('admin.partial.status', ['status' => $employee->employment_status])
            @include('admin.partial.status', ['status' => $employee->approval_status])
        </div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @can('update', $employee)
            <a class="btn btn-outline-primary" href="{{ route('employees.edit', $employee) }}">
                <i class="bi bi-pencil-square me-1"></i>Sửa hồ sơ
            </a>
        @endcan
        @can('create', \App\Models\Contract::class)
            <a class="btn btn-primary" href="{{ route('contracts.create', ['employee_id' => $employee->id]) }}">
                <i class="bi bi-file-earmark-plus me-1"></i>Tạo hợp đồng
            </a>
        @endcan
        <a class="btn btn-light" href="{{ route('employees.index') }}">Quay lại</a>
    </div>
</div>

{{-- Director duyệt hồ sơ mới --}}
@if(auth()->user()->isDirector() && $employee->approval_status === 'pending_approval')
<div class="card border-warning p-3 mb-4 bg-light">
    <h2 class="h6 fw-bold mb-2">Phê duyệt hồ sơ nhân viên mới (Director)</h2>
    <div class="d-flex flex-wrap gap-2 align-items-center">
        <form method="POST" action="{{ route('employees.approve', $employee) }}">
            @csrf
            <button class="btn btn-success btn-sm">Phê duyệt hồ sơ</button>
        </form>
        <form method="POST" action="{{ route('employees.reject', $employee) }}" class="d-flex gap-2">
            @csrf
            <input type="text" name="rejection_reason" class="form-control form-control-sm" placeholder="Nhập lý do từ chối..." required>
            <button class="btn btn-danger btn-sm">Từ chối</button>
        </form>
    </div>
</div>
@endif

@if($employee->rejection_reason)
    <div class="alert alert-danger"><strong>Lý do từ chối hồ sơ:</strong> {{ $employee->rejection_reason }}</div>
@endif

{{-- ================= TAB 1: THÔNG TIN CHI TIẾT ================= --}}
<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card p-4 h-100">
            <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
                @if($employee->avatar)
                    <img src="{{ route('employees.photo', $employee) }}" alt="Ảnh đại diện {{ $employee->full_name }}" class="rounded-circle" width="76" height="76" style="object-fit:cover">
                @else
                    <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center fw-bold fs-3" style="width:76px;height:76px">
                        {{ mb_strtoupper(mb_substr($employee->full_name, 0, 1)) }}
                    </div>
                @endif
                <div>
                    <h2 class="h5 mb-1">1. Thông tin cá nhân &amp; Công việc</h2>
                    <p class="text-muted small mb-0">{{ $employee->department?->name ?? 'Chưa phân phòng ban' }} &bull; {{ $employee->position?->name ?? 'Chưa phân chức vụ' }}</p>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-6"><strong>Mã nhân viên:</strong> {{ $employee->employee_code }}</div>
                <div class="col-md-6"><strong>Số CCCD (12 số):</strong> <code>{{ $employee->citizen_id }}</code></div>
                <div class="col-md-6"><strong>Ngày sinh:</strong> {{ $employee->date_of_birth?->format('d/m/Y') ?? '—' }}</div>
                <div class="col-md-6"><strong>Giới tính:</strong> {{ ['male' => 'Nam', 'female' => 'Nữ', 'other' => 'Khác'][$employee->gender] ?? '—' }}</div>
                <div class="col-md-6"><strong>Số điện thoại:</strong> {{ $employee->phone ?? '—' }}</div>
                <div class="col-md-6"><strong>Email cá nhân:</strong> {{ $employee->personal_email ?? '—' }}</div>
                <div class="col-md-6"><strong>Email công ty:</strong> {{ $employee->work_email }}</div>
                <div class="col-md-6"><strong>Địa chỉ:</strong> {{ $employee->address ?? '—' }}</div>
                <div class="col-md-6"><strong>Phòng ban:</strong> {{ $employee->department?->name ?? '—' }}</div>
                <div class="col-md-6"><strong>Chức vụ:</strong> {{ $employee->position?->name ?? '—' }}</div>
                <div class="col-md-6"><strong>Ngày nhận việc:</strong> {{ $employee->hire_date?->format('d/m/Y') ?? '—' }}</div>
                <div class="col-md-6"><strong>Số ngày phép năm:</strong> <span class="badge bg-info text-dark">{{ $employee->annual_leave_days }} ngày</span></div>
                @if($employee->employment_status === 'resigned' || $employee->resignation_date)
                    <div class="col-md-6 text-danger"><strong>Ngày nghỉ việc:</strong> {{ $employee->resignation_date?->format('d/m/Y') ?? '—' }}</div>
                    <div class="col-md-6 text-danger"><strong>Lý do nghỉ việc:</strong> {{ $employee->resignation_reason ?? '—' }}</div>
                @endif
            </div>
        </div>
    </div>

    {{-- Tài khoản & Xử lý nghỉ việc --}}
    <div class="col-lg-4">
        <div class="card p-4 mb-4">
            <h2 class="h6 fw-bold mb-3">Tài khoản đăng nhập</h2>
            @if($employee->user)
                <p class="mb-1"><strong>Email:</strong> {{ $employee->user->email }}</p>
                <p class="mb-2"><strong>Vai trò:</strong> <span class="badge bg-primary">{{ $employee->user->role?->name }}</span></p>
                <p class="mb-3"><strong>Trạng thái:</strong> @include('admin.partial.status', ['status' => $employee->user->status])</p>
            @else
                <p class="text-muted">Chưa có tài khoản liên kết.</p>
            @endif

            @if(auth()->user()->hasManagementAccess() && $employee->employment_status !== 'resigned')
                @if(!$hasApprovedContract)
                    <div class="alert alert-warning small py-2 mb-0">
                        Cần có hợp đồng đã được Giám đốc phê duyệt (<code>approved</code>) trước khi gửi mail kích hoạt tài khoản.
                    </div>
                @elseif(!$employee->user || $employee->user->status === 'pending_reset')
                    <form method="POST" action="{{ route('employees.account.store', $employee) }}">
                        @csrf
                        <button class="btn btn-warning btn-sm w-100">
                            <i class="bi bi-envelope-paper me-1"></i>Gửi mail kích hoạt tài khoản
                        </button>
                    </form>
                @endif
            @endif
        </div>

        @can('update', $employee)
            @if($employee->employment_status !== 'resigned')
            <div class="card p-4 border-danger">
                <h2 class="h6 fw-bold text-danger mb-3">Xử lý Nghỉ việc</h2>
                <form method="POST" action="{{ route('employees.resign', $employee) }}" onsubmit="return confirm('Xác nhận xử lý nghỉ việc cho nhân viên này?')">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Ngày nghỉ việc <span class="text-danger">*</span></label>
                        <input type="date" name="resignation_date" class="form-control form-control-sm" value="{{ today()->toDateString() }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Lý do nghỉ việc <span class="text-danger">*</span></label>
                        <textarea name="resignation_reason" class="form-control form-control-sm" rows="2" placeholder="Nhập lý do nghỉ việc..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                        {{ auth()->user()->needsApproval() ? 'Đề xuất nghỉ việc (Chờ Director duyệt)' : 'Xác nhận nghỉ việc ngay' }}
                    </button>
                </form>
            </div>
            @endif
        @endcan
    </div>
</div>

{{-- ================= TAB 2: TÀI LIỆU ĐÍNH KÈM (employee_documents) ================= --}}
<div class="card p-4 mb-4">
    <h2 class="h5 mb-3">2. Tài liệu đính kèm (CCCD, CV, Bằng cấp, Giấy tờ khác)</h2>

    @can('create', \App\Models\EmployeeDocument::class)
    <form method="POST" action="{{ route('employees.documents.store', $employee) }}" enctype="multipart/form-data" class="row g-2 align-items-end mb-4 p-3 bg-light rounded border">
        @csrf
        <div class="col-md-3">
            <label class="form-label small fw-semibold">Loại tài liệu</label>
            <select name="doc_type" class="form-select form-select-sm" required>
                <option value="cccd">CCCD / Định danh</option>
                <option value="cv">Sơ yếu lý lịch / CV</option>
                <option value="degree">Bằng cấp / Chứng chỉ</option>
                <option value="other">Tài liệu khác</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label small fw-semibold">Tiêu đề tài liệu</label>
            <input type="text" name="title" class="form-control form-control-sm" placeholder="VD: Bản scan CCCD 2 mặt" required maxlength="255">
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold">Tệp đính kèm (PDF/JPG/PNG, tối đa 5MB)</label>
            <input type="file" name="file" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png" required>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary btn-sm w-100">Tải lên</button>
        </div>
    </form>
    @endcan

    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Loại</th>
                    <th>Tiêu đề</th>
                    <th>Người tải lên</th>
                    <th>Ngày tải lên</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employee->employeeDocuments as $doc)
                <tr>
                    <td>
                        <span class="badge bg-{{ $doc->doc_type === 'cccd' ? 'danger' : 'secondary' }}">
                            {{ $docLabels[$doc->doc_type] ?? $doc->doc_type }}
                        </span>
                    </td>
                    <td class="fw-semibold">{{ $doc->title }}</td>
                    <td>{{ $doc->uploader?->employee?->full_name ?? $doc->uploader?->email ?? '—' }}</td>
                    <td>{{ $doc->created_at?->format('d/m/Y H:i') ?? '—' }}</td>
                    <td>
                        <div class="d-flex gap-1">
                            @can('download', $doc)
                                <a href="{{ route('documents.download', $doc) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-download me-1"></i>Tải xuống
                                </a>
                            @endcan
                            @can('delete', $doc)
                                <form method="POST" action="{{ route('documents.destroy', $doc) }}" onsubmit="return confirm('Xóa tài liệu này?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Xóa</button>
                                </form>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-3">Chưa có tài liệu đính kèm nào.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ================= TAB 3: HỢP ĐỒNG LAO ĐỘNG ================= --}}
<div class="card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h5 mb-0">3. Hợp đồng lao động</h2>
        @can('create', \App\Models\Contract::class)
            <a href="{{ route('contracts.create', ['employee_id' => $employee->id]) }}" class="btn btn-outline-primary btn-sm">
                + Tạo hợp đồng mới
            </a>
        @endcan
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Mã HĐ</th>
                    <th>Loại hợp đồng</th>
                    <th>Hiệu lực</th>
                    <th>Lương cơ bản</th>
                    <th>Phụ cấp</th>
                    <th>Trạng thái HĐ</th>
                    <th>Phê duyệt</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employee->contracts as $contract)
                <tr>
                    <td class="fw-bold"><a href="{{ route('contracts.show', $contract) }}">{{ $contract->contract_code }}</a></td>
                    <td>{{ $contractTypes[$contract->contract_type] ?? $contract->contract_type }}</td>
                    <td>
                        {{ $contract->effective_from?->format('d/m/Y') }}
                        &rarr;
                        {{ $contract->effective_to?->format('d/m/Y') ?? 'Vô thời hạn' }}
                    </td>
                    <td>{{ number_format($contract->base_salary, 0, ',', '.') }} ₫</td>
                    <td>{{ number_format($contract->allowance, 0, ',', '.') }} ₫</td>
                    <td>@include('admin.partial.status', ['status' => $contract->status])</td>
                    <td>@include('admin.partial.status', ['status' => $contract->approval_status])</td>
                    <td>
                        <a href="{{ route('contracts.show', $contract) }}" class="btn btn-sm btn-outline-primary">Chi tiết</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-3">Nhân viên chưa có hợp đồng lao động.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
