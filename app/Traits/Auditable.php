<?php

namespace App\Traits;

use App\Models\AuditLog;

/**
 * Automatically logs create, update, and delete events for a model.
 * Add `use Auditable;` to any model that should be tracked.
 */
trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            static::writeAuditLog('created', $model);
        });

        static::updated(function ($model) {
            $changed = $model->getChanges();
            unset($changed['updated_at']); // ignore timestamp-only changes

            // Strip sensitive fields
            $sensitive = ['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'];
            foreach ($sensitive as $field) {
                unset($changed[$field]);
            }

            if (empty($changed)) {
                return;
            }

            $original = array_intersect_key($model->getOriginal(), $changed);
            foreach ($sensitive as $field) {
                unset($original[$field]);
            }

            static::writeAuditLog('updated', $model, $changed, $original);
        });

        static::deleted(function ($model) {
            static::writeAuditLog('deleted', $model);
        });
    }

    private static function writeAuditLog(string $event, $model, ?array $newValues = null, ?array $oldValues = null): void
    {
        // Skip if running in console (seeders, migrations) to avoid noise
        if (app()->runningInConsole() && !app()->runningUnitTests()) {
            return;
        }

        $user = auth()->user();
        $modelName = class_basename($model);
        $action = strtolower($modelName) . '_' . $event;

        try {
            AuditLog::create([
                'action' => $action,
                'user_id' => $user?->id,
                'tenant_id' => $user?->tenant_id ?? $model->tenant_id ?? null,
                'model_type' => get_class($model),
                'model_id' => $model->getKey(),
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Throwable $e) {
            // Don't let audit logging break the app
            logger()->warning('Audit log failed: ' . $e->getMessage());
        }
    }
}
