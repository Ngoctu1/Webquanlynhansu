<?php

namespace App\Http\Controllers;

use App\Models\AccountInvitation;
use App\Models\AuditLog;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AccountInvitationController extends Controller
{
    public function show(string $token)
    {
        $invitation = $this->validInvitation($token);
        $invitation->load('user.employee');

        return response()->view('auth.activate-account', compact('token', 'invitation'))
            ->header('Cache-Control', 'no-store, private')
            ->header('Referrer-Policy', 'no-referrer');
    }

    public function activate(Request $request, string $token)
    {
        $invitation = $this->validInvitation($token);
        $validated = $request->validate([
            'password' => ['required', 'string', 'confirmed', 'max:72', Password::min(12)->mixedCase()->numbers()],
        ], [
            'password.confirmed' => 'Mật khẩu xác nhận không khớp.',
            'password.min' => 'Mật khẩu cần ít nhất 12 ký tự.',
        ]);

        DB::transaction(function () use ($request, $invitation, $validated, $token) {
            // Cùng thứ tự khóa Employee -> User với luồng cập nhật/tạo tài khoản.
            $candidate = User::find($invitation->user_id);
            $employee = $candidate
                ? Employee::whereKey($candidate->employee_id)->lockForUpdate()->first()
                : null;
            $user = User::whereKey($invitation->user_id)->lockForUpdate()->first();
            $invitation = $this->validInvitation($token, true);
            abort_unless($employee && $user && $user->status === 'pending' &&
                (int) $user->employee_id === (int) $employee->id, 410, 'Liên kết kích hoạt không hợp lệ hoặc đã hết hạn.');

            $oldValues = $user->only(['status', 'email_verified_at', 'password_changed_at']);
            $user->forceFill([
                'password' => Hash::make($validated['password']),
                'status' => 'active',
                'password_changed_at' => now(),
                'email_verified_at' => now(),
            ])->save();
            $invitation->update(['used_at' => now()]);

            AuditLog::create([
                'actor_user_id' => $user->id,
                'action' => 'ACTIVATED',
                'entity_type' => User::class,
                'entity_id' => $user->id,
                'old_values' => $oldValues,
                'new_values' => $user->only(['status', 'email_verified_at', 'password_changed_at']) + [
                    'invitation_id' => $invitation->id,
                    'invitation_used_at' => $invitation->used_at,
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);
        }, 3);

        return redirect()->route('login')->with('success', 'Tài khoản đã được kích hoạt. Bạn có thể đăng nhập.');
    }

    private function validInvitation(string $token, bool $lock = false): AccountInvitation
    {
        abort_unless(preg_match('/\A[A-Za-z0-9]{64}\z/', $token) === 1,
            410, 'Liên kết kích hoạt không hợp lệ hoặc đã hết hạn.');

        $query = AccountInvitation::where('token_hash', hash('sha256', $token))
            ->whereNull('used_at')->where('expires_at', '>', now())
            ->whereHas('user', fn ($query) => $query->where('status', 'pending')->whereHas('employee'));
        if ($lock) {
            $query->lockForUpdate();
        }
        $invitation = $query->first();
        abort_unless($invitation, 410, 'Liên kết kích hoạt không hợp lệ hoặc đã hết hạn.');

        return $invitation;
    }
}
