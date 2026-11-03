@extends('admin.layout.masterlayout')
@section('title', 'Tổng quan nhân sự')

@section('content')
<div class="page-heading">
    <div>
        <span class="section-kicker">KHÔNG GIAN LÀM VIỆC CỦA BẠN</span>
        <h1>Chào {{ auth()->user()->employee?->full_name ?? auth()->user()->email }} <span class="greeting-dot">.</span></h1>
        <p>Cùng nhìn lại đội ngũ và những công việc cần bạn quan tâm.</p>
    </div>
    @if(auth()->user()->canManageHrm())
    <a class="btn btn-primary" href="{{ route('employees.index') }}">
        <i class="bi bi-plus-lg me-2"></i>Thêm nhân viên</a>
    @endif
</div>

<section class="overview-banner">
    <div>
        <span class="banner-tag"><span></span> TỔNG QUAN NHÂN SỰ</span>
        <h2>Mỗi hồ sơ, một hành trình.</h2>
        <p>Thông tin tập trung. Thay đổi được kiểm soát. Đội ngũ luôn kết nối.</p>
        <a href="{{ route('employees.index') }}">Khám phá đội ngũ <i class="bi bi-arrow-up-right ms-2"></i></a>
    </div>
    <div class="banner-art" aria-hidden="true">
        <div class="orbit orbit-one"></div>
        <div class="orbit orbit-two"></div>
        <div class="art-person person-one"><i class="bi bi-person-fill"></i></div>
        <div class="art-person person-two"><i class="bi bi-person-fill"></i></div>
        <div class="art-check"><i class="bi bi-check2"></i></div>
        <span class="art-spark">✦</span>
    </div>
</section>

<div class="row g-3 stats-row">
    <div class="col-sm-6 col-xl-3">
        <a class="stat-card" href="{{ route('employees.index') }}">
            <div class="stat-top"><span>Tổng nhân viên</span><i class="bi bi-people"></i></div>
            <strong>{{ str_pad($employeeCount, 2, '0', STR_PAD_LEFT) }}</strong>
            <small>Hồ sơ trong phạm vi quản lý</small>
        </a>
    </div>
    <div class="col-sm-6 col-xl-3">
        <a class="stat-card" href="{{ route('employees.index') }}">
            <div class="stat-top"><span>Đang làm việc</span><i class="bi bi-person-check"></i></div>
            <strong>{{ str_pad($workingCount, 2, '0', STR_PAD_LEFT) }}</strong>
            <small>Đã xác nhận &amp; có hợp đồng hiệu lực</small>
        </a>
    </div>
    <div class="col-sm-6 col-xl-3">
        <a class="stat-card stat-highlight" href="{{ route('contracts.index', ['status' => 'pending_approval']) }}">
            <div class="stat-top"><span>Hợp đồng chờ duyệt</span><i class="bi bi-hourglass-split"></i></div>
            <strong>{{ str_pad($pendingContracts, 2, '0', STR_PAD_LEFT) }}</strong>
            <small>Yêu cầu cần được xem xét</small>
        </a>
    </div>
    <div class="col-sm-6 col-xl-3">
        <a class="stat-card" href="{{ route('departments.index') }}">
            <div class="stat-top"><span>Phòng ban</span><i class="bi bi-building"></i></div>
            <strong>{{ str_pad($departmentCount ?? 0, 2, '0', STR_PAD_LEFT) }}</strong>
            <small>Đơn vị trong tổ chức</small>
        </a>
    </div>
</div>

@if(!empty($notificationCounts['items']))
<div class="row g-3 mt-1 mb-3">
    @foreach($notificationCounts['items'] as $item)
    <div class="col-md-4">
        <a href="{{ $item['url'] }}" class="card p-3 text-decoration-none h-100 border-start border-4 {{ !empty($item['urgent']) ? 'border-danger' : 'border-primary' }}">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="fw-bold text-dark">{{ $item['label'] }}</div>
                    <div class="small text-muted">{{ $item['subtext'] }}</div>
                </div>
                <span class="badge fs-6 {{ ($item['count'] ?? 0) > 0 ? (!empty($item['urgent']) ? 'bg-danger' : 'bg-primary') : 'bg-secondary' }}">
                    {{ $item['count'] ?? 0 }}
                </span>
            </div>
        </a>
    </div>
    @endforeach
</div>
@endif

<div class="row g-4 mt-1">
    <div class="col-xl-8">
        <section class="panel h-100">
            <div class="panel-heading">
                <div>
                    <h2>Hợp đồng chờ phê duyệt <span class="count-pill">{{ $pendingContracts }}</span></h2>
                    <p>Những hợp đồng đang chờ một quyết định.</p>
                </div>
                <a class="text-link" href="{{ route('contracts.index') }}">Xem tất cả <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>NHÂN VIÊN</th>
                            <th>LOẠI HỢP ĐỒNG</th>
                            <th>NGÀY BẮT ĐẦU</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentContracts ?? [] as $contract)
                        <tr>
                            <td>
                                <div class="person-cell">
                                    <span class="avatar">{{ mb_substr($contract->employee->full_name ?? 'N', 0, 1) }}</span>
                                    <div>
                                        <a href="{{ route('contracts.show', $contract) }}">{{ $contract->employee->full_name ?? 'N/A' }}</a>
                                        <small>{{ $contract->employee->employee_code ?? '' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td><span class="fw-medium">{{ $contract->contract_type ?? 'N/A' }}</span></td>
                            <td class="text-muted">{{ $contract->start_date ? \Carbon\Carbon::parse($contract->start_date)->format('d/m/Y') : 'N/A' }}</td>
                            <td>
                                <a class="icon-link-button" href="{{ route('contracts.show', $contract) }}" aria-label="Xem hợp đồng">
                                    <i class="bi bi-arrow-up-right"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4">
                                <div class="empty-state">
                                    <i class="bi bi-check2-circle"></i>
                                    <p>Không có hợp đồng chờ duyệt.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
    <div class="col-xl-4">
        <section class="panel h-100">
            <div class="panel-heading">
                <div>
                    <h2>Thông tin hệ thống</h2>
                    <p>Vai trò &amp; quyền của bạn</p>
                </div>
                <i class="bi bi-shield-check text-muted"></i>
            </div>
            <div class="department-distribution">
                @if(auth()->user()->canManageHrm())
                <div class="department-row">
                    <div class="d-flex justify-content-between">
                        <span>Quản lý nhân viên</span>
                        <strong><i class="bi bi-check-circle-fill text-success"></i></strong>
                    </div>
                </div>
                @endif
                @if(auth()->user()->canApprove())
                <div class="department-row">
                    <div class="d-flex justify-content-between">
                        <span>Phê duyệt hợp đồng</span>
                        <strong><i class="bi bi-check-circle-fill text-success"></i></strong>
                    </div>
                </div>
                @endif
                @if(auth()->user()->hasRole(\App\Models\Role::HR))
                <div class="department-row">
                    <div class="d-flex justify-content-between">
                        <span>Quản lý nghỉ phép</span>
                        <strong><i class="bi bi-check-circle-fill text-success"></i></strong>
                    </div>
                </div>
                @endif
                @if(auth()->user()->hasRole(\App\Models\Role::ADMIN))
                <div class="department-row">
                    <div class="d-flex justify-content-between">
                        <span>Quản trị hệ thống</span>
                        <strong><i class="bi bi-check-circle-fill text-success"></i></strong>
                    </div>
                </div>
                @endif
                <a class="text-link d-block mt-4" href="{{ route('users.index') }}">Quản lý tài khoản <i class="bi bi-arrow-right"></i></a>
            </div>
        </section>
    </div>
</div>

<section class="panel mt-4">
    <div class="panel-heading">
        <div>
            <h2>Nhân viên mới nhất</h2>
            <p>Những thành viên vừa được thêm vào hệ thống.</p>
        </div>
        <a class="text-link" href="{{ route('employees.index') }}">Danh sách nhân viên <i class="bi bi-arrow-right"></i></a>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>NHÂN VIÊN</th>
                    <th>PHÒNG BAN</th>
                    <th>CHỨC VỤ</th>
                    <th>TRẠNG THÁI</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentEmployees ?? [] as $employee)
                <tr>
                    <td>
                        <div class="person-cell">
                            <span class="avatar">{{ mb_substr($employee->full_name, 0, 1) }}</span>
                            <div>
                                <a href="{{ route('employees.show', $employee) }}">{{ $employee->full_name }}</a>
                                <small>{{ $employee->employee_code }}</small>
                            </div>
                        </div>
                    </td>
                    <td>{{ $employee->department->name ?? 'N/A' }}</td>
                    <td class="text-muted">{{ $employee->position->name ?? 'N/A' }}</td>
                    <td>
                        @php $s = $employee->employment_status ?? 'unknown'; @endphp
                        <span class="badge {{ $s === 'working' ? 'bg-success' : ($s === 'resigned' ? 'bg-danger' : 'bg-secondary') }}">
                            {{ $s === 'working' ? 'Đang làm' : ($s === 'resigned' ? 'Đã nghỉ' : ucfirst($s)) }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('employees.show', $employee) }}" class="icon-link-button" aria-label="Xem {{ $employee->full_name }}">
                            <i class="bi bi-arrow-up-right"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5">
                        <div class="empty-state">
                            <i class="bi bi-people"></i>
                            <p>Chưa có nhân viên nào trong hệ thống.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
