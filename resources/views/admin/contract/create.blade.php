@extends('admin.layout.masterlayout')
@section('title', 'Tạo / Gia hạn hợp đồng lao động')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h4 mb-1">Tạo mới / Gia hạn hợp đồng lao động</h1>
        <p class="text-muted small mb-0">
            @if(auth()->user()->isHR())
                Hợp đồng do HR khởi tạo sẽ ở trạng thái <strong>Chờ Giám đốc duyệt</strong> (<code>pending_approval</code>).
            @else
                Hợp đồng do Giám đốc tạo (kèm file PDF đã ký) sẽ được <strong>Kích hoạt ngay</strong> và tự động kết thúc hợp đồng đang hiệu lực cũ (nếu có).
            @endif
        </p>
    </div>
    <a href="{{ route('contracts.index') }}" class="btn btn-light">Quay lại danh sách</a>
</div>

<div class="card p-4">
    <form method="POST" action="{{ route('contracts.store') }}" enctype="multipart/form-data">
        @csrf
        @include('admin.contract._form')
        <div class="mt-4 d-flex gap-2">
            <button class="btn btn-primary" type="submit">
                {{ auth()->user()->isHR() ? 'Lưu & Trình Giám đốc duyệt' : 'Tạo & Kích hoạt hợp đồng' }}
            </button>
            <a href="{{ route('contracts.index') }}" class="btn btn-light">Hủy</a>
        </div>
    </form>
</div>
@endsection
