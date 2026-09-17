<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AuditLogService
{
    /**
     * Log an action with optional model and metadata.
     */
    public static function log(
        string $action,
        ?User $user = null,
        ?array $metadata = null,
        ?Model $model = null,
        ?array $oldValues = null,
    ): void {
        AuditLog::create([
            'action' => $action,
            'user_id' => $user?->id ?? auth()->id(),
            'tenant_id' => $user?->tenant_id ?? auth()->user()?->tenant_id,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model?->getKey(),
            'old_values' => $oldValues,
            'new_values' => $metadata,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Log a model creation.
     */
    public static function logCreated(string $action, Model $model, ?User $user = null): void
    {
        static::log($action, $user, $model->toArray(), $model);
    }

    /**
     * Log a model update with before/after values.
     */
    public static function logUpdated(string $action, Model $model, ?User $user = null): void
    {
        $changed = $model->getChanges();
        $original = array_intersect_key($model->getOriginal(), $changed);

        // Strip sensitive fields
        $sensitive = ['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'];
        foreach ($sensitive as $field) {
            unset($changed[$field], $original[$field]);
        }

        if (empty($changed)) {
            return;
        }

        static::log($action, $user, $changed, $model, $original);
    }

    /**
     * Log a model deletion.
     */
    public static function logDeleted(string $action, Model $model, ?User $user = null): void
    {
        static::log($action, $user, null, $model, ['deleted' => $model->toArray()]);
    }
}
