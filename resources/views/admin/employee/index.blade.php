@extends('admin.layout.masterlayout')
@section('title', 'Danh sách nhân viên')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h4 mb-0">Danh sách nhân viên</h1>
    @can('create', \App\Models\Employee::class)
        <a href="{{ route('employees.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>Thêm nhân viên
        </a>
    @endcan
</div>

<form class="row g-2 mb-3" method="GET">
    <div class="col-md-4">
        <input
            class="form-control"
            name="q"
            value="{{ request('q') }}"
            placeholder="Tìm theo mã NV, họ tên, email, số điện thoại..."
            maxlength="150"
            aria-label="Tìm nhân viên"
        >
    </div>

    <div class="col-md-2">
        <select class="form-select" name="department_id" aria-label="Phòng ban">
            <option value="">Tất cả phòng ban</option>
            @foreach($departments as $option)
                <option value="{{ $option->id }}" @selected((string) request('department_id') === (string) $option->id)>
                    {{ $option->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-2">
        <select class="form-select" name="position_id" aria-label="Chức vụ">
            <option value="">Tất cả chức vụ</option>
            @foreach($positions as $option)
                <option value="{{ $option->id }}" @selected((string) request('position_id') === (string) $option->id)>
                    {{ $option->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-2">
        <select class="form-select" name="employment_status" aria-label="Trạng thái">
            <option value="">Tất cả trạng thái</option>
            <option value="pending" @selected(request('employment_status') === 'pending')>Chờ nhận việc</option>
            <option value="working" @selected(request('employment_status') === 'working')>Đang làm việc</option>
            <option value="on_leave" @selected(request('employment_status') === 'on_leave')>Nghỉ phép</option>
            <option value="resigned" @selected(request('employment_status') === 'resigned')>Đã nghỉ việc</option>
        </select>
    </div>

    <div class="col-md-2 d-flex gap-1">
        <button class="btn btn-outline-primary">Lọc</button>
        <a href="{{ route('employees.index') }}" class="btn btn-light">Xóa lọc</a>
    </div>
</form>

<div class="card table-responsive">
    <table class="table align-middle mb-0">
        <thead>
            <tr>
                <th>Mã NV</th>
                <th>Ảnh đại diện</th>
                <th>Họ và tên</th>
                <th>Email công ty</th>
                <th>Trạng thái làm việc</th>
                <th>Trạng thái duyệt</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            @forelse($employees as $employee)
                <tr>
                    <td class="fw-bold text-primary">{{ $employee->employee_code }}</td>
                    <td>
                        @if($employee->avatar)
                            <img src="{{ route('employees.photo', $employee) }}" alt="{{ $employee->full_name }}" class="rounded-circle" width="40" height="40" style="object-fit:cover">
                        @else
                            <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center fw-bold" style="width:40px;height:40px">
                                {{ mb_strtoupper(mb_substr($employee->full_name, 0, 1)) }}
                            </div>
                        @endif
                    </td>
                    <td>
                        <div class="fw-semibold">{{ $employee->full_name }}</div>
                        <small class="text-muted">{{ $employee->department?->name ?? '—' }} &bull; {{ $employee->position?->name ?? '—' }}</small>
                    </td>
                    <td>{{ $employee->work_email }}</td>
                    <td>@include('admin.partial.status', ['status' => $employee->employment_status])</td>
                    <td>@include('admin.partial.status', ['status' => $employee->approval_status])</td>
                    <td>
                        <div class="d-flex flex-wrap gap-1">
                            <a href="{{ route('employees.show', $employee) }}" class="btn btn-sm btn-outline-primary">Xem chi tiết</a>
                            @can('update', $employee)
                                <a href="{{ route('employees.edit', $employee) }}" class="btn btn-sm btn-outline-secondary">Sửa</a>
                            @endcan
                            @if(auth()->user()->isDirector() && $employee->approval_status === 'pending_approval')
                                <form class="d-inline" method="POST" action="{{ route('employees.approve', $employee) }}">
                                    @csrf
                                    <button class="btn btn-sm btn-success">Duyệt</button>
                                </form>
                            @endif
                            @can('delete', $employee)
                                <form class="d-inline" action="{{ route('employees.destroy', $employee) }}" method="POST" onsubmit="return confirm('Xác nhận xóa mềm hồ sơ nhân viên này?')">
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
                    <td colspan="7" class="text-center text-muted py-4">Chưa có nhân viên phù hợp.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-3">
    {{ $employees->links('pagination::bootstrap-5') }}
</div>
@endsection