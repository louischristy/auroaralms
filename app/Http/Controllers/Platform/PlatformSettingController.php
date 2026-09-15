<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\PlatformSetting;
use Illuminate\Http\Request;

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
        ]);

        foreach ($validated['settings'] as $group => $keys) {
            foreach ($keys as $key => $value) {
                PlatformSetting::set($key, $value, $group);
            }
        }

        return back()->with('success', 'Platform settings updated successfully.');
    }
}
