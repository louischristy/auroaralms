<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\PlatformSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ClientSettingsController extends Controller
{
    /**
     * Show the tenant email settings form.
     */
    public function emailSettings()
    {
        $tenant = app('current_tenant');
        $settings = $tenant->settings ?? [];
        $tenantSmtp = $settings['smtp'] ?? [];

        $platformDefaults = [
            'host' => PlatformSetting::get('smtp.host', ''),
            'port' => PlatformSetting::get('smtp.port', '587'),
            'from_name' => PlatformSetting::get('email.from_name', 'Auroara LMS'),
            'from_address' => PlatformSetting::get('email.from_address', ''),
        ];

        $usingPlatformDefaults = empty($tenantSmtp);

        return view('client.settings.email', compact('tenantSmtp', 'platformDefaults', 'usingPlatformDefaults'));
    }

    /**
     * Update tenant email / SMTP settings.
     */
    public function updateEmailSettings(Request $request)
    {
        $request->validate([
            'smtp_host' => ['nullable', 'string', 'max:255'],
            'smtp_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'smtp_username' => ['nullable', 'string', 'max:255'],
            'smtp_password' => ['nullable', 'string', 'max:255'],
            'smtp_encryption' => ['nullable', 'string', 'in:tls,ssl,none,'],
            'from_name' => ['nullable', 'string', 'max:255'],
            'from_address' => ['nullable', 'email', 'max:255'],
        ]);

        $tenant = app('current_tenant');
        $settings = $tenant->settings ?? [];

        $smtp = [];
        foreach (['host' => 'smtp_host', 'port' => 'smtp_port', 'username' => 'smtp_username', 'password' => 'smtp_password', 'encryption' => 'smtp_encryption', 'from_name' => 'from_name', 'from_address' => 'from_address'] as $key => $field) {
            $val = $request->input($field);
            if (!empty($val)) {
                $smtp[$key] = $val;
            }
        }

        $settings['smtp'] = $smtp;
        $tenant->settings = $settings;
        $tenant->save();

        // Bust branding/tenant cache
        \Illuminate\Support\Facades\Cache::forget("branding_{$tenant->id}");

        return back()->with('success', 'Email settings updated successfully.');
    }

    /**
     * Reset tenant SMTP to platform defaults.
     */
    public function resetEmailSettings()
    {
        $tenant = app('current_tenant');
        $settings = $tenant->settings ?? [];
        unset($settings['smtp']);
        $tenant->settings = $settings;
        $tenant->save();

        \Illuminate\Support\Facades\Cache::forget("branding_{$tenant->id}");

        return back()->with('success', 'Email settings reset to platform defaults.');
    }

    /**
     * Send a test email using tenant's (or platform's) SMTP settings.
     */
    public function testEmail(Request $request)
    {
        $request->validate(['test_email' => ['required', 'email']]);

        try {
            $tenant = app('current_tenant');
            $settings = $tenant->settings['smtp'] ?? [];

            // Temporarily override mail config for this tenant
            if (!empty($settings['host'])) {
                config(['mail.mailers.smtp.host' => $settings['host']]);
            }
            if (!empty($settings['port'])) {
                config(['mail.mailers.smtp.port' => (int) $settings['port']]);
            }
            if (!empty($settings['username'])) {
                config(['mail.mailers.smtp.username' => $settings['username']]);
            }
            if (!empty($settings['password'])) {
                config(['mail.mailers.smtp.password' => $settings['password']]);
            }
            if (!empty($settings['encryption'])) {
                $enc = $settings['encryption'];
                config(['mail.mailers.smtp.encryption' => $enc === 'none' ? null : $enc]);
            }
            if (!empty($settings['from_name'])) {
                config(['mail.from.name' => $settings['from_name']]);
            }
            if (!empty($settings['from_address'])) {
                config(['mail.from.address' => $settings['from_address']]);
            }

            // Purge the smtp mailer so it picks up new config
            Mail::purge('smtp');

            $tenantName = $tenant->name;
            Mail::raw(
                "This is a test email from {$tenantName}.\n\n" .
                "If you received this, your email configuration is working correctly.\n\n" .
                "Sent at: " . now()->format('Y-m-d H:i:s T'),
                function ($message) use ($request, $tenantName) {
                    $message->to($request->test_email)
                        ->subject("{$tenantName} — Email Test");
                }
            );

            return back()->with('success', 'Test email sent to ' . $request->test_email);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send test email: ' . $e->getMessage());
        }
    }
}
