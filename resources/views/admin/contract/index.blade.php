@extends('admin.layout.masterlayout')
@section('title', 'Quản lý hợp đồng lao động')

@php
    $contractTypes = [
        'probation' => 'Thử việc',
        'fixed_term' => 'Có thời hạn',
        'indefinite' => 'Không xác định thời hạn',
    ];
    $statusOptions = [
        'current' => 'Đang hoạt động & Chờ xử lý (Mặc định)',
        'active' => 'Đang hiệu lực (Active)',
        'pending_approval' => 'Chờ Giám đốc duyệt',
        'rejected' => 'Bị từ chối (Cần chỉnh sửa)',
        'old' => 'Hợp đồng cũ (Hết hạn / Đã chấm dứt)',
        'expired' => 'Đã hết hạn (Expired)',
        'terminated' => 'Đã chấm dứt (Terminated)',
        'all' => 'Tất cả hợp đồng',
    ];
@endphp

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h1 class="h4 mb-1">Quản lý hợp đồng lao động</h1>
        <p class="text-muted small mb-0">
            Theo dõi hợp đồng hiện hành, luồng phê duyệt hợp đồng mới và lịch sử gia hạn hợp đồng.
        </p>
    </div>
    @can('create', \App\Models\Contract::class)
        <a href="{{ route('contracts.create') }}" class="btn btn-primary">
            + Tạo / Gia hạn hợp đồng
        </a>
    @endcan
</div>

<div class="card p-3 mb-3">
    <form method="GET" action="{{ route('contracts.index') }}" class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label small text-muted mb-1">Tìm kiếm</label>
            <input class="form-control" name="q" value="{{ request('q') }}" placeholder="Mã HĐ, Mã NV hoặc Họ tên...">
        </div>
        <div class="col-md-3">
            <label class="form-label small text-muted mb-1">Phạm vi / Trạng thái</label>
            <select name="status" class="form-select">
                @foreach($statusOptions as $key => $label)
                    <option value="{{ $key }}" @selected(($statusFilter ?? 'current') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small text-muted mb-1">Loại hợp đồng</label>
            <select name="contract_type" class="form-select">
                <option value="">Tất cả loại HĐ</option>
                @foreach($contractTypes as $key => $label)
                    <option value="{{ $key }}" @selected(request('contract_type') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small text-muted mb-1">Sắp hết hạn</label>
            <select name="expiring" class="form-select">
                <option value="">Tất cả thời hạn</option>
                <option value="1" @selected(in_array(request('expiring'), ['1', '30'], true))>Trong 1 tháng tới</option>
                <option value="2" @selected(in_array(request('expiring'), ['2', '60'], true))>Trong 2 tháng tới</option>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button class="btn btn-outline-primary w-100" type="submit">Lọc</button>
            <a href="{{ route('contracts.index') }}" class="btn btn-light">Đặt lại</a>
        </div>
    </form>
</div>

<div class="card table-responsive">
    <table class="table align-middle mb-0">
        <thead>
            <tr>
                <th>Mã hợp đồng</th>
                <th>Nhân viên</th>
                <th>Loại HĐ</th>
                <th>Thời hạn hiệu lực</th>
                <th>Lương &amp; Phụ cấp</th>
                <th>Bản PDF ký giấy</th>
                <th>Trạng thái</th>
                <th class="text-end">Thao tác</th>
            </tr>
        </thead>
        <tbody>
            @forelse($contracts as $contract)
                @php
                    $daysRemaining = ($contract->status === 'active' && $contract->effective_to)
                        ? now()->startOfDay()->diffInDays($contract->effective_to->startOfDay(), false)
                        : null;
                @endphp
                <tr>
                    <td>
                        <div class="fw-bold">
                            <a href="{{ route('contracts.show', $contract) }}" class="text-decoration-none">
                                {{ $contract->contract_code }}
                            </a>
                        </div>
                        @if($contract->previousContract)
                            <div class="small text-muted">
                                Gia hạn từ: <a href="{{ route('contracts.show', $contract->previousContract) }}">{{ $contract->previousContract->contract_code }}</a>
                            </div>
                        @endif
                    </td>
                    <td>
                        <div class="fw-semibold">{{ $contract->employee?->full_name }}</div>
                        <div class="small text-muted">
                            {{ $contract->employee?->employee_code }} — {{ $contract->employee?->department?->name ?? '—' }}
                        </div>
                    </td>
                    <td>{{ $contractTypes[$contract->contract_type] ?? $contract->contract_type }}</td>
                    <td>
                        <div>
                            {{ $contract->effective_from?->format('d/m/Y') }}
                            &rarr;
                            {{ $contract->effective_to?->format('d/m/Y') ?? 'Vô thời hạn' }}
                        </div>
                        @if($daysRemaining !== null && $daysRemaining >= 0 && $daysRemaining <= 60)
                            <span class="badge bg-warning text-dark mt-1">Còn {{ $daysRemaining }} ngày</span>
                        @endif
                    </td>
                    <td>
                        <div>Lương CB: <strong>{{ number_format($contract->base_salary, 0, ',', '.') }} ₫</strong> (HS: {{ number_format($contract->coefficient, 2) }})</div>
                        <div class="small text-muted">Phụ cấp: {{ number_format($contract->allowance, 0, ',', '.') }} ₫</div>
                    </td>
                    <td>
                        @if($contract->file_path)
                            <a href="{{ route('contracts.download-pdf', $contract) }}" class="btn btn-sm btn-outline-secondary">
                                Tải PDF
                            </a>
                        @else
                            <span class="badge bg-light text-danger border">Chưa đính kèm PDF</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex flex-column gap-1 align-items-start">
                            <div><small class="text-muted">Hiệu lực:</small> @include('admin.partial.status', ['status' => $contract->status])</div>
                            <div><small class="text-muted">Duyệt:</small> @include('admin.partial.status', ['status' => $contract->approval_status])</div>
                        </div>
                    </td>
                    <td class="text-end">
                        <div class="d-inline-flex gap-1">
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('contracts.show', $contract) }}">Chi tiết</a>
                            @can('update', $contract)
                                <a class="btn btn-sm btn-outline-secondary" href="{{ route('contracts.edit', $contract) }}">Sửa</a>
                            @endcan
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">Không tìm thấy hợp đồng lao động phù hợp.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-3">
    {{ $contracts->links('pagination::bootstrap-5') }}
</div>
@endsection
