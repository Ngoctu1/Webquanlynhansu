<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    private const SECRETS = [
        'password',
        'password_confirmation',
        'current_password',
        'remember_token',
        'activation_token',
        'activation_token_hash',
        'token',
    ];

    public static function log(
        string $action,
        string $entityType,
        $entityId = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?int $userId = null
    ): AuditLog {
        return AuditLog::create([
            'user_id' => $userId ?? Auth::id(),
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId !== null ? (int) $entityId : null,
            'old_values' => $oldValues !== null ? self::sanitize($oldValues) : null,
            'new_values' => $newValues !== null ? self::sanitize($newValues) : null,
            'ip_address' => request()?->ip(),
            'created_at' => now(),
        ]);
    }

    private static function sanitize(array $values): array
    {
        foreach ($values as $key => $value) {
            if (in_array((string) $key, self::SECRETS, true)) {
                unset($values[$key]);
            } elseif (is_array($value)) {
                $values[$key] = self::sanitize($value);
            }
        }

        return $values;
    }
}
