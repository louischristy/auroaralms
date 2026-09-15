<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Global platform-level settings.
 * Stores branding, email config, feature flags, etc.
 * Key-value store with group namespacing.
 */
class PlatformSetting extends Model
{
    protected $fillable = ['group', 'key', 'value', 'type'];

    public $timestamps = false;

    protected $casts = [];

    /**
     * Get a setting value by group.key
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $parts = explode('.', $key, 2);
        $group = count($parts) === 2 ? $parts[0] : 'general';
        $settingKey = count($parts) === 2 ? $parts[1] : $parts[0];

        $setting = static::where('group', $group)
            ->where('key', $settingKey)
            ->first();

        if (!$setting) {
            return $default;
        }

        return self::castValue($setting->value, $setting->type);
    }

    /**
     * Set a setting value.
     */
    public static function set(string $key, mixed $value, string $type = 'string'): void
    {
        $parts = explode('.', $key, 2);
        $group = count($parts) === 2 ? $parts[0] : 'general';
        $settingKey = count($parts) === 2 ? $parts[1] : $parts[0];

        static::updateOrCreate(
            ['group' => $group, 'key' => $settingKey],
            ['value' => is_array($value) ? json_encode($value) : (string) $value, 'type' => $type]
        );
    }

    /**
     * Get all settings in a group as an associative array.
     */
    public static function getGroup(string $group): array
    {
        return static::where('group', $group)
            ->pluck('value', 'key')
            ->toArray();
    }

    private static function castValue(string $value, string $type): mixed
    {
        return match ($type) {
            'boolean', 'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer', 'int' => (int) $value,
            'json', 'array' => json_decode($value, true),
            default => $value,
        };
    }
}
