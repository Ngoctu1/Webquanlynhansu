<!doctype html>
<html lang="vi"><head><meta charset="utf-8"></head><body>
<p>Chào {{ $employeeName }},</p>
<p>Bạn nhận được yêu cầu kích hoạt tài khoản hoặc đặt lại mật khẩu trên Hệ thống Quản lý Nhân sự (HRM).</p>
<p>Email đăng nhập: <strong>{{ $loginEmail }}</strong></p>
<p>Vui lòng mở liên kết dưới đây để thiết lập mật khẩu của bạn:</p>
<p><a href="{{ $activationUrl }}">{{ $activationUrl }}</a></p>
<p>Liên kết có hiệu lực đến {{ $expiresAt->format('d/m/Y H:i') }} ({{ $expiresAt->timezoneName }}), tối đa 48 giờ.</p>
<p>Nếu bạn không nhận ra yêu cầu này, hãy liên hệ phòng Nhân sự.</p>
</body></html>
