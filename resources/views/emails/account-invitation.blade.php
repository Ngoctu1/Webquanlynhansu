<!DOCTYPE html>
<html lang="vi">
<head><meta charset="UTF-8"><title>Kích hoạt tài khoản HRM</title></head>
<body style="font-family:Arial,sans-serif;line-height:1.6;color:#1e293b;">
    <h2>Xin chào {{ $employeeName }},</h2>
    <p>Tài khoản hệ thống quản lý nhân sự của bạn đã được tạo.</p>
    <p>Tên đăng nhập: <strong>{{ $username }}</strong></p>
    <p>Vui lòng nhấn vào liên kết dưới đây để kích hoạt tài khoản và thiết lập mật khẩu.</p>
    <p><a href="{{ $activationUrl }}" style="display:inline-block;background:#2563eb;color:#fff;padding:12px 20px;border-radius:8px;text-decoration:none;">Kích hoạt tài khoản</a></p>
    <p>Liên kết có hiệu lực trong 24 giờ, đến {{ $expiresAt }} và chỉ sử dụng được một lần.</p>
    <p>Nếu không nhấn được nút, hãy mở liên kết: <a href="{{ $activationUrl }}">{{ $activationUrl }}</a></p>
</body>
</html>
