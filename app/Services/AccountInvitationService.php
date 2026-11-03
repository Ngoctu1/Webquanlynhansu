<?php

namespace App\Services;

use App\Mail\AccountInvitationMail;
use App\Models\{Employee, Role, User};
use Illuminate\Support\Facades\{DB, Gate, Hash, Log, Mail};
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class AccountInvitationService
{
    public function send(User $actor, Employee $employee): bool
    {
        $targetUser = DB::transaction(function () use ($employee) {
            $employee = Employee::lockForUpdate()->findOrFail($employee->id);

            if ($employee->employment_status === 'resigned') {
                throw ValidationException::withMessages([
                    'invitation' => 'Nhân viên đã nghỉ việc không thể kích hoạt tài khoản.',
                ]);
            }

            if (!$employee->contracts()->where('approval_status', 'approved')->exists()) {
                throw ValidationException::withMessages([
                    'invitation' => 'Nhân viên phải có hợp đồng đã được Director duyệt trước khi gửi mail kích hoạt.',
                ]);
            }

            $user = $employee->user()->lockForUpdate()->first();

            if (!$user) {
                $loginEmail = $employee->work_email ?: $employee->personal_email;
                if (User::where('email', $loginEmail)->exists()) {
                    throw ValidationException::withMessages([
                        'invitation' => 'Email công ty đã được dùng cho một tài khoản khác.',
                    ]);
                }

                $user = User::create([
                    'employee_id' => $employee->id,
                    'role_id' => Role::where('name', Role::EMPLOYEE)->firstOrFail()->id,
                    'email' => $loginEmail,
                    'password' => null,
                    'status' => 'pending_reset',
                ]);

                AuditLogger::log('create_user', 'user', $user->id, null, [
                    'employee_id' => $user->employee_id,
                    'email' => $user->email,
                    'role_id' => $user->role_id,
                    'status' => $user->status,
                ]);
            }

            return $user;
        }, 3);

        return $this->sendToUser($actor, $targetUser);
    }

    public function sendToUser(User $actor, User $targetUser): bool
    {
        Gate::forUser($actor)->authorize('sendActivationOrResetMail', $targetUser);

        if ($targetUser->status === 'pending_reset' && $targetUser->employee_id !== null) {
            $hasApprovedContract = $targetUser->employee()
                ->whereHas('contracts', fn ($q) => $q->where('approval_status', 'approved'))
                ->exists();

            if (!$hasApprovedContract) {
                throw ValidationException::withMessages([
                    'invitation' => 'Nhân viên phải có hợp đồng đã được Director duyệt trước khi gửi mail kích hoạt.',
                ]);
            }
        }

        $plainToken = Str::random(64);
        $expiresAt = now()->addHours(48);

        DB::transaction(function () use ($targetUser, $plainToken, $expiresAt) {
            $user = User::lockForUpdate()->findOrFail($targetUser->id);
            $oldStatus = $user->status;

            $user->update([
                'status' => 'pending_reset',
                'activation_token' => Hash::make($plainToken),
                'token_expires_at' => $expiresAt,
            ]);

            AuditLogger::log(
                'send_activation_mail',
                'user',
                $user->id,
                ['status' => $oldStatus],
                ['email' => $user->email, 'status' => 'pending_reset', 'token_expires_at' => $expiresAt->toDateTimeString()]
            );
        }, 3);

        $activationUrl = route('account.activate', [
            'email' => $targetUser->email,
            'token' => $plainToken,
        ]);

        try {
            $recipientName = $targetUser->employee?->full_name ?? $targetUser->email;
            Mail::to($targetUser->email)->send(new AccountInvitationMail(
                $recipientName,
                $targetUser->email,
                $activationUrl,
                $expiresAt
            ));
        } catch (Throwable $e) {
            Log::warning('Không gửi được SMTP thực tế, link kích hoạt: ' . $activationUrl . ' | Lỗi: ' . $e->getMessage());
        }

        return true;
    }
}
