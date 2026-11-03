<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;

class AuditService
{
    public static function record(string $action, Model $model, array $old = [], ?array $new = null, ?int $actor = null): void
    {
        AuditLogger::log(
            $action,
            strtolower(class_basename($model)),
            $model->getKey(),
            !empty($old) ? $old : null,
            $new ?? $model->getAttributes(),
            $actor
        );
    }
}
