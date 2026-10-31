<!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Đăng nhập — Nhân Sự</title>
<link rel="stylesheet" href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="{{ asset('css/extra.css') }}">
</head>
<body class="login-page">
<main class="login-layout">
    <section class="login-story">
        <div class="brand">
            <span class="brand-icon"><i class="bi bi-intersect"></i></span>
            <span>nhân sự<span class="brand-caption">PEOPLE &amp; OPERATIONS</span></span>
        </div>
        <div class="login-story-content">
            <span class="eyebrow">MỌI HÀNH TRÌNH ĐỀU BẮT ĐẦU TỪ CON NGƯỜI</span>
            <h1>Chăm chút hồ sơ.<br>Vững vàng<br><span>đội ngũ.</span></h1>
            <p>Một không gian để quản lý nhân sự, lưu giữ lịch sử<br class="d-none d-xl-block"> và phê duyệt những thay đổi quan trọng.</p>
            <div class="login-illustration" aria-hidden="true">
                <div class="illustration-card">
                    <i class="bi bi-person-vcard"></i>
                    <div>
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                    <b><i class="bi bi-check2"></i></b>
                </div>
                <div class="illustration-label"><i class="bi bi-shield-check"></i> Rõ ràng từng bước</div>
            </div>
        </div>
        <small>HỒ SƠ • HỢP ĐỒNG • PHÊ DUYỆT</small>
    </section>

    <section class="login-form-section">
        <div class="login-form">
            <span class="section-kicker">CHÀO MỪNG TRỞ LẠI</span>
            <h2>Đăng nhập</h2>
            <p class="text-muted mb-4">Tiếp tục công việc quản lý nhân sự của bạn.</p>

            @if(session('success'))
                <div class="alert alert-success" role="status">{{ session('success') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('login.submit') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="email">Email công việc</label>
                    <input class="form-control" id="email" name="email" type="email"
                           value="{{ old('email') }}" placeholder="ten@congty.vn"
                           autocomplete="username" required autofocus>
                </div>
                <div class="mb-4">
                    <label class="form-label" for="password">Mật khẩu</label>
                    <div class="input-group">
                        <input class="form-control" id="password" name="password" type="password"
                               autocomplete="current-password" required>
                        <button type="button" class="btn btn-outline-secondary" id="togglePassword" aria-label="Hiện mật khẩu">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                        <label class="form-check-label text-muted" for="remember" style="font-size:0.875rem;">Ghi nhớ đăng nhập</label>
                    </div>
                </div>
                <button class="btn btn-primary w-100 py-3" type="submit">Đăng nhập <i class="bi bi-arrow-right ms-2"></i></button>
            </form>

            <div class="login-access">
                <i class="bi bi-shield-lock"></i>
                <span>Dành cho Quản trị viên, Nhân sự và Giám đốc.<br>Liên hệ quản trị viên nếu bạn cần hỗ trợ truy cập.</span>
            </div>
        </div>
        <small class="login-footer">Nhân Sự • Hệ thống quản lý hồ sơ nội bộ</small>
    </section>
</main>
<script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
