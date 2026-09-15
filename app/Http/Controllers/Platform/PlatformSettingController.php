<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\PlatformSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PlatformSettingController extends Controller
{
    public function edit()
    {
        $settings = [
            'brand' => PlatformSetting::getGroup('brand'),
            'general' => PlatformSetting::getGroup('general'),
            'email' => PlatformSetting::getGroup('email'),
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
            PlatformSetting::set('logo_path', '/uploads/branding/' . $logoName, 'brand');
        }

        // Handle favicon upload
        if ($request->hasFile('favicon')) {
            $favicon = $request->file('favicon');
            $faviconName = 'favicon-' . time() . '.' . $favicon->getClientOriginalExtension();
            $favicon->move(public_path('uploads/branding'), $faviconName);
            PlatformSetting::set('favicon_path', '/uploads/branding/' . $faviconName, 'brand');
        }

        // Save text settings
        if (isset($validated['settings'])) {
            foreach ($validated['settings'] as $group => $keys) {
                foreach ($keys as $key => $value) {
                    // Skip logo_path and favicon_path as they're handled above
                    if (in_array($key, ['logo_path', 'favicon_path'])) {
                        continue;
                    }
                    PlatformSetting::set($key, $value, $group);
                }
            }
        }

        return back()->with('success', 'Platform settings updated successfully.');
    }
}
