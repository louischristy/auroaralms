<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class SecurityServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->environment('production')) {
            config([
                'session.secure' => true,
            ]);
        }

        config([
            'session.same_site' => 'lax',
            'session.http_only' => true,
        ]);
    }
}
