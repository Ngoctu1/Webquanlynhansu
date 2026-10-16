<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditService
{
    private const SECRETS = ['password', 'password_confirmation', 'remember_token', 'activation_token_hash', 'token', 'activation_token'];

    public static function record(string $action, Model $model, array $old = [], ?array $new = null, ?int $actor = null): void
    {
        AuditLog::create([
            'user_id' => $actor ?? Auth::id(),
            'action' => $action,
            'auditable_type' => $model->getMorphClass(),
            'auditable_id' => $model->getKey(),
            'old_values' => self::sanitize($old),
            'new_values' => self::sanitize($new ?? $model->getAttributes()),
            'ip_address' => request()->ip(),
            'user_agent' => mb_substr((string) request()->userAgent(), 0, 255),
            'created_at' => now(),
        ]);
    }

    private static function sanitize(array $values): array
    {
        foreach ($values as $key => $value) {
            if (in_array($key, self::SECRETS, true)) {
                unset($values[$key]);
            } elseif (is_array($value)) {
                $values[$key] = self::sanitize($value);
            }
        }
        return $values;
    }
}
