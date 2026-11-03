<?php

namespace App\Http\Controllers;

use App\Http\Requests\ActivateAccountRequest;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Hash};

class AccountInvitationController extends Controller
{
    public function show(Request $request, ?string $token = null)
    {
        $plainToken = $token ?? (string) $request->query('token', '');
        $email = (string) $request->query('email', '');

        $user = $this->resolveUser($plainToken, $email);
        abort_unless($user, 410, 'Liên kết không hợp lệ, đã dùng hoặc đã hết hạn.');

        return response()
            ->view('auth.activate-account', [
                'token' => $plainToken,
                'email' => $user->email,
                'user' => $user,
            ])
            ->header('Cache-Control', 'no-store')
            ->header('Referrer-Policy', 'no-referrer');
    }

    public function activate(ActivateAccountRequest $request, ?string $token = null)
    {
        $data = $request->validated();
        $plainToken = $token ?? (string) ($data['token'] ?? $request->query('token', ''));
        $email = (string) ($data['email'] ?? $request->query('email', ''));

        DB::transaction(function () use ($plainToken, $email, $data) {
            $user = $this->resolveUser($plainToken, $email, true);
            abort_unless($user, 410, 'Liên kết không hợp lệ, đã dùng hoặc đã hết hạn.');

            $oldUserValues = ['status' => $user->status];

            $user->update([
                'password' => Hash::make($data['password']),
                'status' => 'active',
                'activation_token' => null,
                'token_expires_at' => null,
            ]);

            if ($user->employee_id !== null) {
                $employee = $user->employee()->lockForUpdate()->first();
                if ($employee && $employee->employment_status === 'pending') {
                    $oldEmpStatus = $employee->employment_status;
                    $employee->update(['employment_status' => 'working']);
                    AuditLogger::log(
                        'employee_working_on_activation',
                        'employee',
                        $employee->id,
                        ['employment_status' => $oldEmpStatus],
                        ['employment_status' => 'working'],
                        $user->id
                    );
                }
            }

            AuditLogger::log(
                'activate_account',
                'user',
                $user->id,
                $oldUserValues,
                ['status' => 'active'],
                $user->id
            );
        });

        return redirect()
            ->route('login')
            ->with('success', 'Tài khoản đã được kích hoạt và đặt mật khẩu thành công. Bạn có thể đăng nhập.')
            ->header('Cache-Control', 'no-store')
            ->header('Referrer-Policy', 'no-referrer');
    }

    protected function resolveUser(string $plainToken, string $email = '', bool $lock = false): ?User
    {
        if ($plainToken === '' || strlen($plainToken) < 16) {
            return null;
        }

        $query = User::whereNotNull('activation_token')
            ->where('token_expires_at', '>', now());

        if ($email !== '') {
            $query->where('email', $email);
        }

        if ($lock) {
            $query->lockForUpdate();
        }

        $candidates = $query->get();

        foreach ($candidates as $candidate) {
            $stored = (string) $candidate->activation_token;
            if (
                Hash::check($plainToken, $stored)
                || hash_equals($stored, hash('sha256', $plainToken))
                || hash_equals($stored, $plainToken)
            ) {
                return $candidate;
            }
        }

        return null;
    }
}
