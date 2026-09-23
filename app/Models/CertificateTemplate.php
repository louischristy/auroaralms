<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CertificateTemplate extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'name', 'description', 'is_default', 'is_active',
        'config', 'created_by', 'tenant_id',
    ];

    protected $casts = [
        'config' => 'array',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Default config values for a new template.
     */
    public static function defaultConfig(): array
    {
        return [
            'border_style' => 'classic',
            'border_color' => '#2B4C7E',
            'accent_color' => '#3A7BD5',
            'background_color' => '#ffffff',
            'font_style' => 'classic',
            'custom_title' => 'Certificate of Completion',
            'custom_subtitle' => 'Cybersecurity Awareness Training',
            'show_score' => true,
            'show_logo' => true,
            'show_certificate_number' => true,
            'show_expiry' => true,
            'show_date_issued' => true,
            'show_course_duration' => false,
            'show_signatures' => false,
            'signatures' => [],
            'issuing_authority' => '',
            'issuing_authority_title' => '',
            'footer_text' => '',
            'decorative_elements' => 'corners',
        ];
    }

    /**
     * Get config with defaults merged in.
     */
    public function getFullConfig(): array
    {
        return array_merge(self::defaultConfig(), $this->config ?? []);
    }

    /**
     * Get the platform default template.
     */
    public static function getDefault(): ?self
    {
        return static::withoutTenantScope()
            ->whereNull('tenant_id')
            ->where('is_default', true)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Get template for a specific tenant (falls back to platform default).
     */
    public static function getForTenant(?int $tenantId): ?self
    {
        if ($tenantId) {
            // Check if tenant has a selected template
            $tenant = Tenant::find($tenantId);
            if ($tenant?->selected_certificate_template_id) {
                $template = static::withoutTenantScope()->find($tenant->selected_certificate_template_id);
                if ($template?->is_active) {
                    return $template;
                }
            }

            // Check for tenant-specific template
            $tenantTemplate = static::withoutTenantScope()
                ->where('tenant_id', $tenantId)
                ->where('is_active', true)
                ->first();
            if ($tenantTemplate) {
                return $tenantTemplate;
            }
        }

        return static::getDefault();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
