@extends('admin.layout.masterlayout')
@section('title', 'Quản lý tài khoản')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4"><h1 class="h4 mb-0">Quản lý tài khoản</h1></div>
<div class="card table-responsive"><table class="table align-middle"><thead><tr><th>Nhân viên</th><th>Email đăng nhập</th><th>Vai trò</th><th>Trạng thái</th><th>Đăng nhập gần nhất</th><th>Thao tác</th></tr></thead><tbody>
@forelse($users as $account)<tr>
<td>
    @if($account->employee)
        <a href="{{ route('employees.show', $account->employee) }}">{{ $account->employee->full_name }} ({{ $account->employee->employee_code }})</a>
    @else
        <span class="text-muted">Tài khoản kỹ thuật</span>
    @endif
</td>
<td>{{ $account->email }}</td>
<td><span class="badge bg-info text-dark">{{ $account->role?->name }}</span></td>
<td>@include('admin.partial.status', ['status' => $account->status])</td>
<td>{{ $account->last_login_at?->format('d/m/Y H:i') ?? '—' }}</td>
<td>
<div class="d-flex flex-wrap gap-2">
@can('assignRole', $account)
    @if(auth()->id() !== $account->id)
    <form method="POST" action="{{ route('users.role', $account) }}" class="d-flex gap-1">@csrf @method('PATCH')
    <select name="role_id" class="form-select form-select-sm" style="width:auto" aria-label="Vai trò của {{ $account->email }}">@foreach($roles as $role)<option value="{{ $role->id }}" @selected((int)$account->role_id === (int)$role->id)>{{ $role->name }}</option>@endforeach</select>
    <button class="btn btn-outline-primary btn-sm">Đổi vai trò</button></form>
    @endif
@endcan

@can('sendActivationOrResetMail', $account)
    <form method="POST" action="{{ route('users.send-activation', $account) }}">@csrf
        <button class="btn btn-outline-warning btn-sm">
            {{ $account->status === 'pending_reset' ? 'Gửi mail kích hoạt' : 'Cấp lại mật khẩu' }}
        </button>
    </form>
@endcan

@if(auth()->user()->hasManagementAccess() && auth()->id() !== $account->id && in_array($account->status, ['active', 'locked']) && (!auth()->user()->isHR() || (!$account->isAdmin() && !$account->isDirector())))
    <form method="POST" action="{{ route('users.status', $account) }}">@csrf @method('PATCH')
        <input type="hidden" name="status" value="{{ $account->status === 'active' ? 'locked' : 'active' }}">
        <button class="btn btn-outline-secondary btn-sm">{{ $account->status === 'active' ? 'Khóa tài khoản' : 'Mở khóa' }}</button>
    </form>
@endif
</div>
</td></tr>
@empty<tr><td colspan="6">Chưa có tài khoản.</td></tr>@endforelse</tbody></table></div>
<div class="mt-3">{{ $users->links('pagination::bootstrap-5') }}</div>
@endsection
