<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  mixed  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // 1. Kiểm tra xem người dùng đã đăng nhập chưa
        if (!Auth::check()) {
            return redirect('login');
        }

        $user = Auth::user();

        // 2. Kiểm tra role_id của user có nằm trong danh sách các role được phép (truyền qua route) hay không
        if (in_array($user->role_id, $roles)) {
            return $next($request);
        }

        // 3. Nếu không có quyền, báo lỗi 403 Forbidden (hoặc redirect tùy logic)
        abort(403, 'Bạn không có quyền truy cập khu vực này.');
    }
}
