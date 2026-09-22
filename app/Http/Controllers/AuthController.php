<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Hiển thị trang đăng nhập.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Xử lý đăng nhập.
     */
    public function login(Request $request)
    {
        // Validate input
        $credentials = $request->validate([
            'username' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string'],
        ], [
            'username.required' => 'Vui lòng nhập tên đăng nhập.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
        ]);

        $remember = $request->filled('remember');

        // Chỉ tài khoản active được xác thực; không tiết lộ trạng thái khi thất bại.
        if (Auth::attempt([...$credentials, 'status' => 'active'], $remember)) {
            // Ngăn chặn session fixation
            $request->session()->regenerate();

            $user = Auth::user();
            
            // Cập nhật thời gian đăng nhập lần cuối
            $user->last_login_at = now();
            $user->save();

            return $this->redirectBasedOnRole($user);
        }

        // Nếu thông tin không chính xác
        return back()->withErrors([
            'username' => 'Thông tin đăng nhập không hợp lệ hoặc tài khoản chưa được phép đăng nhập.',
        ])->onlyInput('username');
    }

    /**
     * Đăng xuất.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Điều hướng theo Role của User.
     */
    protected function redirectBasedOnRole($user)
    {
        // Role 1 (Admin) và Role 2 (HR) -> /admin
        if ($user->role_id == 1 || $user->role_id == 2) {
            return redirect('/admin');
        }
        
        // Role 3 (Employee) -> /user
        if ($user->role_id == 3) {
            return redirect('/user');
        }

        // Fallback an toàn (nếu có role khác không xác định)
        Auth::logout();
        return redirect()->route('login')->withErrors(['username' => 'Quyền truy cập không hợp lệ.']);
    }
}
