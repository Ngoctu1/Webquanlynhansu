<?php
namespace App\Services;

use App\Mail\AccountInvitationMail;
use App\Models\{Employee, Role, User};
use Illuminate\Support\Facades\{DB, Mail};
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class AccountInvitationService
{
    public function send(User $actor, Employee $employee): bool
    {
        abort_unless($actor->canManageHrm(), 403);
        // Refuse transports that discard mail or write the activation URL into logs.
        if (!$this->delivers((string) config('mail.default'))) {
            throw ValidationException::withMessages(['invitation' => 'Mailer hiện tại không gửi thư thực. Cần cấu hình SMTP hoặc dịch vụ gửi mail trước.']);
        }
        $rawToken = Str::random(64);
        $user = DB::transaction(function () use ($actor, $employee, $rawToken) {
            $employee = Employee::lockForUpdate()->findOrFail($employee->id);
            if ($employee->employment_status === 'resigned' || !$employee->contracts()->where('status', 'approved')->exists()) {
                throw ValidationException::withMessages(['invitation' => 'Nhân viên phải chưa nghỉ việc và có hợp đồng đã được Director hoặc Admin duyệt.']);
            }
            $user = $employee->user()->lockForUpdate()->first();
            if ($user) {
                return null; // Duplicate click: do not rotate token or send another email.
            }
            if (!$user) {
                if (User::where('email', $employee->personal_email)->exists()) {
                    throw ValidationException::withMessages(['invitation' => 'Email đã được dùng cho một tài khoản khác.']);
                }
                $user = User::create([
                    'employee_id' => $employee->id, 'role_id' => Role::where('name', Role::EMPLOYEE)->firstOrFail()->id,
                    'email' => $employee->personal_email, 'password' => null, 'status' => 'pending_reset',
                ]);
                AuditService::record('USER_CREATED', $user);
            }
            $user->update(['activation_token_hash' => hash('sha256', $rawToken), 'activation_expires_at' => now()->addHours(24)]);
            AuditService::record('ACCOUNT_INVITATION_CREATED', $user, [], ['status' => $user->status, 'activation_expires_at' => $user->activation_expires_at]);
            return $user;
        }, 3);
        if (!$user) { return false; }

        // Data has committed before contacting the SMTP server. Never queue raw token in a DB payload.
        try {
            $sent = Mail::to($user->email)->send(new AccountInvitationMail(
                $employee->full_name, $user->email, route('account.activate', ['token' => $rawToken]), $user->activation_expires_at
            ));
            if ($sent === null) { throw new \RuntimeException('Mail not accepted'); }
        } catch (Throwable) {
            // An SMTP timeout can be ambiguous: invalidate this link, leaving a clear pending account.
            DB::transaction(function () use ($user) {
                $current = User::lockForUpdate()->findOrFail($user->id);
                if ($current->status === 'pending_reset' && hash_equals((string) $current->activation_token_hash, $user->activation_token_hash)) {
                    $current->update(['activation_token_hash' => null, 'activation_expires_at' => null]);
                    AuditService::record('ACCOUNT_INVITATION_FAILED', $current, [], ['status' => 'pending_reset']);
                }
            });
            throw ValidationException::withMessages(['invitation' => 'Chưa xác nhận gửi được email. Tài khoản đang chờ kích hoạt và liên kết đã bị vô hiệu hóa. Cần kiểm tra cấu hình mail và xử lý gửi lại có kiểm soát; thao tác lặp không tự gửi thêm thư.']);
        }
        DB::transaction(fn () => AuditService::record('ACCOUNT_INVITATION_SENT', $user, [], ['email' => $user->email]));
        return true;
    }

    private function delivers(string $mailer, array $seen = []): bool
    {
        if (in_array($mailer, $seen, true)) { return false; }
        $config = config('mail.mailers.'.$mailer, []);
        if (in_array($config['transport'] ?? '', ['failover', 'roundrobin'], true)) {
            $children = $config['mailers'] ?? [];
            if (!$children) { return false; }
            foreach ($children as $child) {
                if (!$this->delivers($child, [...$seen, $mailer])) { return false; }
            }
            return true;
        }
        return in_array($config['transport'] ?? '', ['smtp', 'ses', 'ses-v2', 'postmark', 'resend', 'mailgun', 'sendmail'], true);
    }
}
