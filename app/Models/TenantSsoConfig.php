<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\Auditable;

class TenantSsoConfig extends Model
{
    use Auditable;

    protected $fillable = [
        'tenant_id', 'provider', 'client_id', 'client_secret',
        'tenant_identifier', 'allowed_domains', 'is_active',
        'auto_provision', 'force_sso',
    ];

    protected $casts = [
        'allowed_domains' => 'array',
        'is_active'       => 'boolean',
        'auto_provision'  => 'boolean',
        'force_sso'       => 'boolean',
        'client_secret'   => 'encrypted',
    ];

    protected $hidden = ['client_secret'];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Supported SSO providers.
     */
    public static function providers(): array
    {
        return [
            'google'    => 'Google Workspace',
            'microsoft' => 'Microsoft 365 / Azure AD',
        ];
    }

    /**
     * Check if an email domain is allowed for this SSO config.
     */
    public function isDomainAllowed(string $email): bool
    {
        if (empty($this->allowed_domains)) {
            return true; // no restriction
        }

        $domain = strtolower(substr(strrchr($email, '@'), 1));
        return in_array($domain, array_map('strtolower', $this->allowed_domains));
    }
}
