<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
    )
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('training:send-reminders')->dailyAt('08:00');
        $schedule->command('queue:work --stop-when-empty --max-time=55')->everyMinute()->withoutOverlapping();
    })
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\InjectBranding::class,
            \App\Http\Middleware\SecurityHeaders::class,
            \App\Http\Middleware\TenantMailConfig::class,
        ]);
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'resolve.tenant' => \App\Http\Middleware\ResolveTenant::class,
            'inject.branding' => \App\Http\Middleware\InjectBranding::class,
            '2fa.verified' => \App\Http\Middleware\EnsureTwoFactorVerified::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
