<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || $user->status !== 'active') {
            $status = $user?->status;

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $message = match ($status) {
                'pending_reset' => 'Tài khoản của bạn chưa được kích hoạt hoặc đang chờ đặt lại mật khẩu.',
                'locked' => 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.',
                default => 'Tài khoản của bạn không hợp lệ hoặc chưa được kích hoạt.',
            };

            return redirect('/login')->withErrors(['email' => $message]);
        }

        return $next($request);
    }
}
