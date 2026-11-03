@extends('admin.layout.masterlayout')
@section('title', 'Chỉnh sửa hợp đồng lao động')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h4 mb-1">Chỉnh sửa hợp đồng: {{ $contract->contract_code }}</h1>
        <p class="text-muted small mb-0">
            @if(in_array($contract->status, ['expired', 'terminated'], true))
                <span class="text-warning fw-semibold">Hợp đồng cũ ({{ $contract->status }}):</span> Chỉ Giám đốc mới có quyền chỉnh sửa trực tiếp.
            @elseif(auth()->user()->can('requiresApproval', $contract))
                Hợp đồng đang hiệu lực (<code>active</code>): Thay đổi của HR sẽ được lưu vào <strong>Đề xuất thay đổi (Change Request)</strong> chờ Giám đốc phê duyệt.
            @elseif(auth()->user()->isHR())
                Sau khi chỉnh sửa, hợp đồng sẽ được chuyển lại về trạng thái <strong>Chờ Giám đốc duyệt</strong> (<code>pending_approval</code>).
            @else
                Thay đổi của Giám đốc sẽ được áp dụng trực tiếp.
            @endif
        </p>
    </div>
    <a href="{{ route('contracts.show', $contract) }}" class="btn btn-light">Quay lại chi tiết</a>
</div>

<div class="card p-4">
    <form method="POST" action="{{ route('contracts.update', $contract) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.contract._form')
        <div class="mt-4 d-flex gap-2">
            <button class="btn btn-primary" type="submit">
                @if(auth()->user()->can('requiresApproval', $contract))
                    Gửi đề xuất chỉnh sửa
                @elseif(auth()->user()->isHR())
                    Lưu &amp; Trình duyệt lại
                @else
                    Lưu thay đổi trực tiếp
                @endif
            </button>
            <a href="{{ route('contracts.show', $contract) }}" class="btn btn-light">Hủy</a>
        </div>
    </form>
</div>
@endsection
