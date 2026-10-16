<?php
namespace App\Http\Controllers;

use App\Http\Requests\ActivateAccountRequest;

use App\Models\User;
use App\Services\AuditService;
use Illuminate\Support\Facades\{DB, Hash};

class AccountInvitationController extends Controller
{
    public function show(string $token)
    {
        $user = $this->find($token)->first();
        abort_unless($user, 410, 'Liên kết không hợp lệ, đã dùng hoặc đã hết hạn.');
        return response()->view('auth.activate-account', compact('token', 'user'))
            ->header('Cache-Control', 'no-store')->header('Referrer-Policy', 'no-referrer');
    }

    public function activate(ActivateAccountRequest $request, string $token)
    {
        $data = $request->validated();
        DB::transaction(function () use ($token, $data) {
            $user = $this->find($token)->lockForUpdate()->first();
            abort_unless($user, 410, 'Liên kết không hợp lệ, đã dùng hoặc đã hết hạn.');
            $old = ['status' => $user->status];
            $user->update([
                'password' => Hash::make($data['password']), 'status' => 'active', 'activated_at' => now(),
                'activation_token_hash' => null, 'activation_expires_at' => null,
            ]);
            AuditService::record('ACCOUNT_ACTIVATED', $user, $old, ['status' => 'active', 'activated_at' => $user->activated_at], $user->id);
        });
        return redirect()->route('login')->with('success', 'Tài khoản đã được kích hoạt. Bạn có thể đăng nhập.')
            ->header('Cache-Control', 'no-store')->header('Referrer-Policy', 'no-referrer');
    }

    private function find(string $token)
    {
        abort_unless(preg_match('/^[A-Za-z0-9]{64}$/D', $token), 410, 'Liên kết không hợp lệ.');
        return User::where('activation_token_hash', hash('sha256', $token))->where('status', 'pending_reset')
            ->whereNull('activated_at')->where('activation_expires_at', '>', now());
    }
}
