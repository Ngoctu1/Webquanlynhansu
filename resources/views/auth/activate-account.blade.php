<!doctype html>
<html lang="vi"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="referrer" content="no-referrer">
<title>Thiết lập mật khẩu — HRM</title>
<link rel="stylesheet" href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}">
</head>
<body class="bg-light"><main class="container py-5" style="max-width:520px"><div class="card p-4 shadow-sm">
<h1 class="h4">Thiết lập mật khẩu tài khoản</h1><p class="text-muted">{{ $user->email }}</p>
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<form method="POST" action="{{ route('account.activate.submit', ['token' => $token, 'email' => $user->email]) }}">@csrf
<input type="hidden" name="email" value="{{ $user->email }}">
<input type="hidden" name="token" value="{{ $token }}">
<label for="password" class="form-label">Mật khẩu mới (ít nhất 8 ký tự, tối đa 72 byte; gồm chữ hoa, chữ thường và số)</label>
<input class="form-control mb-3" type="password" id="password" name="password" minlength="8" maxlength="72" autocomplete="new-password" required>
<label for="password_confirmation" class="form-label">Nhập lại mật khẩu</label>
<input class="form-control mb-3" type="password" id="password_confirmation" name="password_confirmation" minlength="8" maxlength="72" autocomplete="new-password" required>
<button class="btn btn-primary w-100">Xác nhận &amp; Kích hoạt tài khoản</button>
</form></div></main></body></html>
