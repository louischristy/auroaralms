<?php

namespace Database\Seeders;

use App\Models\PlatformSetting;
use Illuminate\Database\Seeder;

class PlatformSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // Brand settings
            ['group' => 'brand', 'key' => 'platform_name', 'value' => 'Auroara LMS', 'type' => 'string'],
            ['group' => 'brand', 'key' => 'company_name', 'value' => 'Auroara Technologies Sdn Bhd', 'type' => 'string'],
            ['group' => 'brand', 'key' => 'primary_color', 'value' => '#2B4C7E', 'type' => 'string'],
            ['group' => 'brand', 'key' => 'secondary_color', 'value' => '#3A7BD5', 'type' => 'string'],
            ['group' => 'brand', 'key' => 'accent_color', 'value' => '#5BC0EB', 'type' => 'string'],
            ['group' => 'brand', 'key' => 'neutral_color', 'value' => '#A8A9AD', 'type' => 'string'],
            ['group' => 'brand', 'key' => 'logo_path', 'value' => '/images/auroara-logo.svg', 'type' => 'string'],
            ['group' => 'brand', 'key' => 'favicon_path', 'value' => '/images/favicon.ico', 'type' => 'string'],
            ['group' => 'brand', 'key' => 'show_powered_by', 'value' => '1', 'type' => 'bool'],
            ['group' => 'brand', 'key' => 'powered_by_text', 'value' => 'Powered by Auroara Technologies', 'type' => 'string'],

            // General settings
            ['group' => 'general', 'key' => 'default_timezone', 'value' => 'Asia/Kuala_Lumpur', 'type' => 'string'],
            ['group' => 'general', 'key' => 'date_format', 'value' => 'd M Y', 'type' => 'string'],
            ['group' => 'general', 'key' => 'max_upload_size_mb', 'value' => '10', 'type' => 'int'],
            ['group' => 'general', 'key' => 'maintenance_mode', 'value' => '0', 'type' => 'bool'],

            // Email settings
            ['group' => 'email', 'key' => 'from_name', 'value' => 'Auroara LMS', 'type' => 'string'],
            ['group' => 'email', 'key' => 'from_address', 'value' => 'noreply@auroara.com', 'type' => 'string'],
            ['group' => 'email', 'key' => 'welcome_email_enabled', 'value' => '1', 'type' => 'bool'],
            ['group' => 'email', 'key' => 'course_reminder_days', 'value' => '3', 'type' => 'int'],

            // SMTP settings
            ['group' => 'smtp', 'key' => 'host', 'value' => '', 'type' => 'string'],
            ['group' => 'smtp', 'key' => 'port', 'value' => '587', 'type' => 'string'],
            ['group' => 'smtp', 'key' => 'username', 'value' => '', 'type' => 'string'],
            ['group' => 'smtp', 'key' => 'password', 'value' => '', 'type' => 'string'],
            ['group' => 'smtp', 'key' => 'encryption', 'value' => 'tls', 'type' => 'string'],

            // Security settings
            ['group' => 'security', 'key' => 'password_min_length', 'value' => '8', 'type' => 'int'],
            ['group' => 'security', 'key' => 'session_lifetime_minutes', 'value' => '120', 'type' => 'int'],
            ['group' => 'security', 'key' => 'max_login_attempts', 'value' => '5', 'type' => 'int'],
            ['group' => 'security', 'key' => 'lockout_minutes', 'value' => '15', 'type' => 'int'],
        ];

        foreach ($settings as $setting) {
            PlatformSetting::updateOrCreate(
                ['group' => $setting['group'], 'key' => $setting['key']],
                ['value' => $setting['value'], 'type' => $setting['type']]
            );
        }
    }
}
