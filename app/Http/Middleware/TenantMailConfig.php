<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Response;

/**
 * Applies tenant-level SMTP overrides on top of platform defaults.
 * This runs after MailConfigServiceProvider has set the platform defaults.
 */
class TenantMailConfig
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = app()->bound('current_tenant') ? app('current_tenant') : null;

        if ($tenant) {
            $smtp = $tenant->settings['smtp'] ?? [];

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
                if (!empty($smtp['from_name'])) {
                    config(['mail.from.name' => $smtp['from_name']]);
                }
                if (!empty($smtp['from_address'])) {
                    config(['mail.from.address' => $smtp['from_address']]);
                }

                // Purge cached mailer so it picks up overridden config
                Mail::purge('smtp');
            }
        }

        return $next($request);
    }
}
