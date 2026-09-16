<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;

class AuditLogService
{
    public static function log(string $action, ?User $user = null, ?array $metadata = null): void
    {
        AuditLog::create([
            'action' => $action,
            'user_id' => $user?->id,
            'tenant_id' => $user?->tenant_id,
            'old_values' => null,
            'new_values' => $metadata,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
