<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public const SUPPORTED = ['en' => 'English', 'ms' => 'Bahasa Melayu'];

    public function handle(Request $request, Closure $next): Response
    {
        app()->setLocale($this->resolve($request));

        return $next($request);
    }

    private function resolve(Request $request): string
    {
        $supported = array_keys(self::SUPPORTED);
        $user = $request->user();

        // Explicit choice (user preference column if present, then session) wins over tenant default.
        $candidates = [
            $user?->locale ?? null,
            $request->hasSession() ? $request->session()->get('locale') : null,
            $user?->tenant?->locale ?? (app()->bound('current_tenant') ? app('current_tenant')?->locale : null),
            $request->getPreferredLanguage($supported),
        ];

        foreach ($candidates as $locale) {
            if ($locale && in_array($locale, $supported, true)) {
                return $locale;
            }
        }

        return config('app.locale', 'en');
    }
}
