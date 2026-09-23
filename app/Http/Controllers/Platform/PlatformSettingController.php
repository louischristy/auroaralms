<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\PlatformSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class PlatformSettingController extends Controller
{
    public function edit()
    {
        $settings = [
            'brand' => PlatformSetting::getGroup('brand'),
            'general' => PlatformSetting::getGroup('general'),
            'email' => PlatformSetting::getGroup('email'),
            'smtp' => PlatformSetting::getGroup('smtp'),
            'security' => PlatformSetting::getGroup('security'),
        ];

        return view('platform.settings.edit', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings' => ['required', 'array'],
            'settings.*.*' => ['nullable', 'string', 'max:1000'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048'],
            'favicon' => ['nullable', 'image', 'mimes:png,ico,svg', 'max:512'],
        ]);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            $logoName = 'platform-logo-' . time() . '.' . $logo->getClientOriginalExtension();
            $logo->move(public_path('uploads/branding'), $logoName);
            PlatformSetting::set('brand.logo_path', '/uploads/branding/' . $logoName);
        }

        // Handle favicon upload
        if ($request->hasFile('favicon')) {
            $favicon = $request->file('favicon');
            $faviconName = 'favicon-' . time() . '.' . $favicon->getClientOriginalExtension();
            $favicon->move(public_path('uploads/branding'), $faviconName);
            PlatformSetting::set('brand.favicon_path', '/uploads/branding/' . $faviconName);
        }

        // Save text settings
        if (isset($validated['settings'])) {
            foreach ($validated['settings'] as $group => $keys) {
                foreach ($keys as $key => $value) {
                    if (in_array($key, ['logo_path', 'favicon_path'])) {
                        continue;
                    }
                    PlatformSetting::set($group . '.' . $key, $value);
                }
            }
        }

        return back()->with('success', 'Platform settings updated successfully.');
    }

    /**
     * Send a test email to verify SMTP configuration.
     */
    public function testEmail(Request $request)
    {
        $request->validate([
            'test_email' => ['required', 'email'],
        ]);

        try {
            $platformName = PlatformSetting::get('brand.platform_name', 'Auroara LMS');

            Mail::raw(
                "This is a test email from {$platformName}.\n\n" .
                "If you received this, your SMTP configuration is working correctly.\n\n" .
                "Sent at: " . now()->format('Y-m-d H:i:s T'),
                function ($message) use ($request, $platformName) {
                    $message->to($request->test_email)
                        ->subject("{$platformName} — SMTP Test Email");
                }
            );

            return back()->with('success', 'Test email sent successfully to ' . $request->test_email . '. Please check the inbox.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send test email: ' . $e->getMessage());
        }
    }
}
