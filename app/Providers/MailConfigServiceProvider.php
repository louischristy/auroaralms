<?php

namespace App\Providers;

use App\Models\PlatformSetting;
use Illuminate\Support\ServiceProvider;

/**
 * Overrides Laravel's mail config at runtime using platform_settings.
 * Tenant-level overrides are applied via TenantMailMiddleware.
 */
class MailConfigServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Only override if we're not running in console (migrations, etc.)
        // and the platform_settings table exists
        if ($this->app->runningInConsole()) {
            return;
        }

        try {
            $smtp = PlatformSetting::getGroup('smtp');

            if (!empty($smtp)) {
                if (!empty($smtp['host'])) {
                    config(['mail.mailers.smtp.host' => $smtp['host']]);
                }
                if (!empty($smtp['port'])) {
                    config(['mail.mailers.smtp.port' => (int) $smtp['port']]);
                }
                if (!empty($smtp['username'])) {
                    config(['mail.mailers.smtp.username' => $smtp['username']]);
                }
                if (!empty($smtp['password'])) {
                    config(['mail.mailers.smtp.password' => $smtp['password']]);
                }
                if (!empty($smtp['encryption'])) {
                    $enc = $smtp['encryption'];
                    config(['mail.mailers.smtp.encryption' => $enc === 'none' ? null : $enc]);
                }
            }

            $email = PlatformSetting::getGroup('email');
            if (!empty($email['from_address'])) {
                config(['mail.from.address' => $email['from_address']]);
            }
            if (!empty($email['from_name'])) {
                config(['mail.from.name' => $email['from_name']]);
            }
        } catch (\Exception $e) {
            // Table may not exist yet during initial migrations
        }
    }
}
