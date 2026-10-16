@php
    $labels = ['active' => 'Hoạt động', 'inactive' => 'Ngừng sử dụng', 'pending' => 'Chờ nhận việc',
        'working' => 'Đang làm việc', 'on_leave' => 'Đang nghỉ phép', 'resigned' => 'Đã nghỉ việc',
        'pending_reset' => 'Chờ kích hoạt', 'locked' => 'Đã khóa', 'draft' => 'Bản nháp',
        'pending_approval' => 'Chờ duyệt', 'approved' => 'Đã duyệt', 'rejected' => 'Bị từ chối',
        'published' => 'Đã công bố', 'checked_in' => 'Đã vào ca', 'completed' => 'Đã tính công', 'adjusted' => 'HR đã xác nhận'];
    $color = in_array($status, ['active', 'working', 'approved', 'published', 'adjusted']) ? 'success'
        : (in_array($status, ['locked', 'rejected', 'resigned']) ? 'danger' : 'secondary');
@endphp
<span class="badge bg-{{ $color }}">{{ $labels[$status] ?? $status }}</span>
