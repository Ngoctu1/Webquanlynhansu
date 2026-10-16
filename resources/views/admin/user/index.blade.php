@extends('admin.layout.masterlayout')
@section('title', 'Quản lý tài khoản')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4"><h1 class="h4 mb-0">Quản lý tài khoản</h1></div>
<div class="card table-responsive"><table class="table align-middle"><thead><tr><th>Nhân viên</th><th>Email đăng nhập</th><th>Vai trò</th><th>Trạng thái</th><th>Kích hoạt</th><th>Thao tác</th></tr></thead><tbody>
@forelse($users as $account)<tr><td><a href="{{ route('employees.show', $account->employee) }}">{{ $account->employee->full_name }}</a></td><td>{{ $account->email }}</td><td>{{ $account->role->name }}</td><td>@include('admin.partial.status', ['status' => $account->status])</td><td>{{ $account->activated_at?->format('d/m/Y H:i') ?? '—' }}</td><td>
@if(auth()->user()->hasRole(\App\Models\Role::ADMIN) && auth()->id() !== $account->id)
<form method="POST" action="{{ route('users.role', $account) }}" class="d-flex gap-2 mb-2">@csrf @method('PATCH')
<select name="role_id" class="form-select form-select-sm" aria-label="Vai trò của {{ $account->email }}">@foreach($roles as $role)<option value="{{ $role->id }}" @selected((int)$account->role_id === (int)$role->id)>{{ $role->name }}</option>@endforeach</select><button class="btn btn-outline-primary btn-sm">Đổi vai trò</button></form>
@endif
@if(auth()->user()->canManageHrm())@if((auth()->user()->hasRole(\App\Models\Role::ADMIN) || $account->hasRole(\App\Models\Role::EMPLOYEE)) && auth()->id() !== $account->id && $account->activated_at && in_array($account->status, ['active', 'locked']))
<form method="POST" action="{{ route('users.status', $account) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="{{ $account->status === 'active' ? 'locked' : 'active' }}"><button class="btn btn-outline-secondary btn-sm">{{ $account->status === 'active' ? 'Khóa tài khoản' : 'Mở khóa' }}</button></form>@endif @endif</td></tr>
@empty<tr><td colspan="6">Chưa có tài khoản.</td></tr>@endforelse</tbody></table></div><div class="mt-3">{{ $users->links('pagination::bootstrap-5') }}</div><p class="text-muted">Gửi lời mời từ trang chi tiết nhân viên sau khi hợp đồng được duyệt.</p>
@endsection
