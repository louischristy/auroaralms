<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Custom Blade directives for role checks
        Blade::if('role', function (string $role) {
            return auth()->check() && auth()->user()->hasRole($role);
        });

        Blade::if('anyrole', function (string $roles) {
            return auth()->check() && auth()->user()->hasAnyRole(explode(',', $roles));
        });
    }
}
