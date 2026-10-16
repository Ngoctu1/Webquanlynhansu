<!doctype html>
<html lang="vi"><head><meta charset="utf-8"></head><body>
<p>Chào {{ $employeeName }},</p>
<p>Tài khoản hệ thống quản lý nhân sự của bạn đã được tạo.</p>
<p>Email đăng nhập: <strong>{{ $loginEmail }}</strong></p>
<p>Vui lòng mở liên kết dưới đây để kích hoạt tài khoản và tự thiết lập mật khẩu.</p>
<p><a href="{{ $activationUrl }}">Kích hoạt tài khoản</a></p>
<p>Liên kết có hiệu lực đến {{ $expiresAt->format('d/m/Y H:i') }} ({{ $expiresAt->timezoneName }}), tối đa 24 giờ.</p>
<p>Nếu bạn không nhận ra yêu cầu này, hãy liên hệ phòng Nhân sự.</p>
</body></html>
