<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Hệ thống Quản lý Nhân sự</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            padding: 2.5rem 2rem;
            width: 100%;
            max-width: 420px;
            border: 1px solid #e2e8f0;
        }
        .login-logo {
            text-align: center;
            margin-bottom: 1rem;
        }
        .login-logo i {
            font-size: 3.5rem;
            color: #2563eb;
        }
        .login-title {
            font-size: 1.35rem;
            font-weight: 700;
            color: #1e293b;
            text-align: center;
            margin-bottom: 0.25rem;
        }
        .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37,99,235,.1);
        }
        .btn-primary {
            background-color: #2563eb;
            border-color: #2563eb;
            padding: 0.65rem;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.2s;
        }
        .btn-primary:hover {
            background-color: #1d4ed8;
            border-color: #1d4ed8;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="login-logo">
            <i class="bi bi-shield-lock-fill"></i>
        </div>
        <h2 class="login-title">Hệ thống Quản lý Nhân sự</h2>
        <p class="text-center mb-4 text-secondary" style="font-size: 0.95rem;">Đăng nhập để tiếp tục</p>

        @if(session('success'))
            <div class="alert alert-success" role="alert">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger" style="font-size: 0.875rem; border-radius: 8px;">
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('login.submit') }}" method="POST">
            @csrf
            
            <div class="mb-3">
                <label for="username" class="form-label fw-semibold" style="font-size: 0.875rem; color: #475569;">Username</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-secondary border-end-0" style="border-radius: 8px 0 0 8px;">
                        <i class="bi bi-person"></i>
                    </span>
                    <input type="text" id="username" name="username" class="form-control border-start-0 ps-0" 
                           placeholder="Nhập tên đăng nhập" value="{{ old('username') }}" 
                           style="border-radius: 0 8px 8px 0;" required autofocus>
                </div>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label fw-semibold" style="font-size: 0.875rem; color: #475569;">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-secondary border-end-0" style="border-radius: 8px 0 0 8px;">
                        <i class="bi bi-lock"></i>
                    </span>
                    <input type="password" id="password" name="password" class="form-control border-start-0 ps-0" 
                           placeholder="Nhập mật khẩu" style="border-radius: 0 8px 8px 0;" required>
                </div>
            </div>

            <div class="mb-4 d-flex justify-content-between align-items-center">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label class="form-check-label text-secondary" for="remember" style="font-size: 0.875rem;">
                        Ghi nhớ đăng nhập
                    </label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 mb-2">
                <i class="bi bi-box-arrow-in-right me-1"></i> Đăng nhập
            </button>
        </form>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
