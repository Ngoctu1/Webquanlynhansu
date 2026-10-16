@extends('admin.layout.masterlayout')
@section('title', 'Tổng quan nhân sự')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4"><h1 class="h4 mb-0">Tổng quan nhân sự</h1></div>
<div class="row g-3">
@foreach([['label'=>'Nhân viên','count'=>$employeeCount,'route'=>'employees.index'],['label'=>'Đang làm việc','count'=>$workingCount,'route'=>'employees.index'],['label'=>'Hợp đồng chờ duyệt','count'=>$pendingContracts,'route'=>'contracts.index'],['label'=>'Kỳ lương chờ duyệt','count'=>$pendingPayrolls,'route'=>'payrolls.index']] as $card)
<div class="col-md-6 col-xl-3"><a href="{{ route($card['route']) }}" class="card p-4 text-decoration-none"><span class="text-muted">{{ $card['label'] }}</span><strong class="fs-2">{{ $card['count'] }}</strong></a></div>@endforeach</div>
<div class="card p-4 mt-4"><h2 class="h5">Công việc</h2>
@if(auth()->user()->canManageHrm())<p>Quản lý nhân viên, hợp đồng, chấm công và bảng lương. Hợp đồng cần Director hoặc Admin phê duyệt trước khi gửi lời mời kích hoạt.</p>@endif
@if(auth()->user()->canApprove())<p>Bạn có thể tạo, chỉnh sửa dữ liệu nhân sự và phê duyệt hoặc từ chối hợp đồng, bảng lương đang chờ duyệt.</p>@endif
@if(auth()->user()->hasRole(\App\Models\Role::ADMIN))<p>Quản lý vai trò và trạng thái tài khoản tại mục Tài khoản.</p>@endif</div>
@endsection
