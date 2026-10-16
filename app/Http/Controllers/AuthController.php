<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin() { return view('auth.login'); }

    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();
        if (!Auth::attempt([...$credentials, 'status' => 'active'], $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Email hoặc mật khẩu không hợp lệ, hoặc tài khoản chưa được phép đăng nhập.'])->onlyInput('email');
        }
        $request->session()->regenerate();
        $user = $request->user();
        if (!$user->hasRole(Role::HR, Role::DIRECTOR, Role::EMPLOYEE, Role::ADMIN)) {
            return $this->logout($request);
        }
        $user->update(['last_login_at' => now()]);
        if ($user->hasRole(Role::EMPLOYEE)) {
            return redirect()->route('user.attendances');
        }
        return redirect($user->homePath());
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
