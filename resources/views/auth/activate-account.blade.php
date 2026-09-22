<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="referrer" content="no-referrer">
    <title>Kích hoạt tài khoản - Quản lý Nhân sự</title>
    <style>
        * { box-sizing: border-box; }
        body { margin:0; min-height:100vh; display:grid; place-items:center; padding:24px; background:#f8fafc; color:#1e293b; font-family:Arial,sans-serif; }
        main { width:100%; max-width:460px; padding:32px; background:#fff; border:1px solid #e2e8f0; border-radius:12px; }
        h1 { font-size:1.4rem; } p { line-height:1.6; } label { display:block; margin:18px 0 8px; }
        input, button { width:100%; padding:12px; border-radius:8px; border:1px solid #cbd5e1; font-size:1rem; }
        button { margin-top:24px; background:#2563eb; color:#fff; cursor:pointer; }
        .errors { padding:12px; background:#fee2e2; color:#991b1b; border-radius:8px; } small { color:#475569; }
    </style>
</head>
<body>
    <main>
        <h1>Kích hoạt tài khoản HRM</h1>
        <p>Xin chào {{ $invitation->user->employee->full_name }}.<br>Tên đăng nhập: <strong>{{ $invitation->user->username }}</strong></p>
        @if($errors->any())
            <div class="errors" role="alert">
                @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
            </div>
        @endif
        <form method="POST" action="{{ route('account.activate.submit', ['token' => $token]) }}">
            @csrf
            <label for="password">Mật khẩu</label>
            <input type="password" id="password" name="password" autocomplete="new-password" minlength="12" maxlength="72" required autofocus aria-describedby="password-help">
            <small id="password-help">12–72 ký tự, gồm chữ hoa, chữ thường và số.</small>
            <label for="password_confirmation">Xác nhận mật khẩu</label>
            <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password" minlength="12" maxlength="72" required>
            <button type="submit">Đặt mật khẩu và kích hoạt</button>
        </form>
    </main>
</body>
</html>
