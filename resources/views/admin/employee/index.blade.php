@extends('admin.layout.masterlayout')
@section('title', 'Danh sách nhân viên')
@section('styles')
<style>
    /* ---- Bộ lọc ---- */
    .filter-card {
        background: #fff;
        border: 1px solid var(--border-color);
        border-radius: var(--radius);
        padding: 18px 22px;
        margin-bottom: 20px;
        box-shadow: var(--shadow-sm);
    }

    /* ---- Bảng dữ liệu ---- */
    .table-card {
        background: #fff;
        border: 1px solid var(--border-color);
        border-radius: var(--radius);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
    }

    .table-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 22px;
        border-bottom: 1px solid var(--border-color);
        background: #fafbfc;
        flex-wrap: wrap;
        gap: 10px;
    }

    .hrm-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.875rem;
    }

    .hrm-table thead th {
        background: #f8fafc;
        color: #64748b;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .05em;
        padding: 11px 14px;
        border-bottom: 1px solid var(--border-color);
        white-space: nowrap;
    }

    .hrm-table tbody tr:hover td {
        background: #f8faff;
    }

    .hrm-table tbody td {
        padding: 12px 14px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
        color: #1e293b;
    }

    .hrm-table tbody tr:last-child td { border-bottom: none; }

    /* Avatar trong bảng */
    .emp-avatar {
        width: 34px;
        height: 34px;
        border-radius: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.73rem;
        font-weight: 700;
        color: #fff;
        background: linear-gradient(135deg, #2563eb, #7c3aed);
        flex-shrink: 0;
    }

    .emp-name { font-weight: 600; font-size: 0.875rem; color: #1e293b; }
    .emp-id   { font-size: 0.73rem; color: #94a3b8; }

    /* Badge trạng thái */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 0.73rem;
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 20px;
        white-space: nowrap;
    }
    .status-badge i { font-size: 7px; }

    .status-active   { background: #d1fae5; color: #065f46; }
    .status-leave    { background: #fef3c7; color: #92400e; }
    .status-resigned { background: #fee2e2; color: #991b1b; }
    .status-probation{ background: #e0e7ff; color: #3730a3; }

    /* Nút thao tác */
    .action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        border-radius: 8px;
        font-size: 0.85rem;
        border: none;
        transition: background .15s, color .15s;
        text-decoration: none;
    }

    .btn-view   { background: #dbeafe; color: #2563eb; }
    .btn-view:hover   { background: #2563eb; color: #fff; }
    .btn-edit   { background: #d1fae5; color: #059669; }
    .btn-edit:hover   { background: #059669; color: #fff; }
    .btn-delete { background: #fee2e2; color: #dc2626; }
    .btn-delete:hover { background: #dc2626; color: #fff; }

    /* Empty state */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #94a3b8;
    }

    .empty-state i { font-size: 3rem; margin-bottom: 14px; display: block; }
</style>
@endsection
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4"><h1 class="h4 mb-0">Danh sách nhân viên</h1></div>
@if(auth()->user()->canManageHrm())<a href="{{ route('employees.create') }}" class="btn btn-primary mb-3">Thêm nhân viên</a>@endif
<form class="row g-2 mb-3" method="GET"><div class="col-md-4"><input class="form-control" name="q" value="{{ request('q') }}" placeholder="Mã, họ tên, email, điện thoại" maxlength="150" aria-label="Tìm nhân viên"></div>
<div class="col-md-2"><select class="form-select" name="department_id" aria-label="Phòng ban"><option value="">Tất cả phòng ban</option>@foreach($departments as $option)<option value="{{ $option->id }}" @selected((string)request('department_id') === (string)$option->id)>{{ $option->name }}</option>@endforeach</select></div><div class="col-md-2"><select class="form-select" name="position_id" aria-label="Chức vụ"><option value="">Tất cả chức vụ</option>@foreach($positions as $option)<option value="{{ $option->id }}" @selected((string)request('position_id') === (string)$option->id)>{{ $option->name }}</option>@endforeach</select></div>
<div class="col-md-2"><select class="form-select" name="employment_status" aria-label="Trạng thái"><option value="">Tất cả trạng thái</option><option value="pending" @selected(request('employment_status') === 'pending')>Chờ nhận việc</option><option value="working" @selected(request('employment_status') === 'working')>Đang làm việc</option><option value="on_leave" @selected(request('employment_status') === 'on_leave')>Nghỉ phép</option><option value="resigned" @selected(request('employment_status') === 'resigned')>Nghỉ việc</option></select></div><div class="col-md-2"><button class="btn btn-outline-primary">Lọc</button> <a href="{{ route('employees.index') }}" class="btn btn-light">Xóa lọc</a></div></form>
<div class="card table-responsive"><table class="table align-middle"><thead><tr><th>STT</th><th>Mã</th><th>Họ tên</th><th>Email</th><th>Điện thoại</th><th>Phòng ban</th><th>Chức vụ</th><th>Trạng thái</th><th>Tài khoản</th><th>Thao tác</th></tr></thead><tbody>
@forelse($employees as $employee)<tr><td>{{ $employees->firstItem() + $loop->index }}</td><td>{{ $employee->employee_code }}</td><td>{{ $employee->full_name }}</td>
<td>{{ $employee->personal_email }}@if($employee->work_email)<div class="small text-muted">{{ $employee->work_email }}</div>@endif</td><td>{{ $employee->phone ?? '—' }}</td><td>{{ $employee->department->name }}</td><td>{{ $employee->position->name }}</td>
<td>@include('admin.partial.status', ['status' => $employee->employment_status])</td><td>@if($employee->user){{ $employee->user->email }}<div>{{ $employee->user->role->name }}</div>@include('admin.partial.status', ['status' => $employee->user->status])@else Chưa có tài khoản @endif</td>
<td><a href="{{ route('employees.show', $employee) }}" class="btn btn-sm btn-outline-primary">Xem</a>
@if(auth()->user()->canManageHrm())<a href="{{ route('employees.edit', $employee) }}" class="btn btn-sm btn-outline-secondary">Sửa</a>
@if($employee->employment_status !== 'resigned')<form class="d-inline" action="{{ route('employees.destroy', $employee) }}" method="POST" onsubmit="return confirm('Đánh dấu nhân viên nghỉ việc?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Nghỉ việc</button></form>@endif @endif</td></tr>
@empty<tr><td colspan="10" class="text-center">Chưa có nhân viên phù hợp.</td></tr>@endforelse
</tbody></table></div><div class="mt-3">{{ $employees->links('pagination::bootstrap-5') }}</div>
@endsection
