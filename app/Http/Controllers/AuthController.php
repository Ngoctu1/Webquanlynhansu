<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Hash, RateLimiter};
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();
        $throttleKey = Str::transliterate(Str::lower($credentials['email']) . '|' . $request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()
                ->withErrors(['email' => "Bạn đã nhập sai quá số lần quy định. Vui lòng thử lại sau {$seconds} giây."])
                ->onlyInput('email');
        }

        $user = User::with('role')->where('email', $credentials['email'])->first();

        if (!$user || !$user->password || !Hash::check($credentials['password'], $user->password)) {
            if ($user && $user->status === 'pending_reset') {
                return back()
                    ->withErrors(['email' => 'Tài khoản chưa được kích hoạt. Vui lòng kiểm tra email để đặt mật khẩu.'])
                    ->onlyInput('email');
            }

            if ($user && $user->status === 'locked') {
                return back()
                    ->withErrors(['email' => 'Tài khoản của bạn đã bị khóa.'])
                    ->onlyInput('email');
            }

            RateLimiter::hit($throttleKey, 60);

            return back()
                ->withErrors(['email' => 'Email hoặc mật khẩu không chính xác.'])
                ->onlyInput('email');
        }

        if ($user->status === 'pending_reset') {
            return back()
                ->withErrors(['email' => 'Tài khoản chưa được kích hoạt. Vui lòng kiểm tra email để đặt mật khẩu.'])
                ->onlyInput('email');
        }

        if ($user->status === 'locked') {
            return back()
                ->withErrors(['email' => 'Tài khoản của bạn đã bị khóa.'])
                ->onlyInput('email');
        }

        if ($user->status !== 'active') {
            return back()
                ->withErrors(['email' => 'Tài khoản không hợp lệ để đăng nhập.'])
                ->onlyInput('email');
        }

        RateLimiter::clear($throttleKey);
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        $user->update(['last_login_at' => now()]);
        AuditLogger::log('login', 'user', $user->id, null, ['email' => $user->email], $user->id);

        if ($user->hasManagementAccess()) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('user.dashboard');
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            AuditLogger::log('logout', 'user', Auth::id());
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function changePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'confirmed', 'max:72', Password::min(8)->mixedCase()->numbers()],
        ]);

        $user = $request->user();

        if (!Hash::check($data['current_password'], (string) $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Mật khẩu hiện tại không chính xác.',
            ]);
        }

        $user->update([
            'password' => Hash::make($data['password']),
        ]);

        AuditLogger::log('change_password', 'user', $user->id);

        return back()->with('success', 'Đã đổi mật khẩu cá nhân thành công.');
    }
}
