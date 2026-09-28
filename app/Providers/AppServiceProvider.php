<?php

namespace App\Providers;

use App\Models\CourseEnrollment;
use App\Models\LessonCompletion;
use App\Models\QuizAttempt;
use App\Models\SurveyResponse;
use App\Observers\GamificationObserver;
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
        // Gamification points
        foreach ([LessonCompletion::class, CourseEnrollment::class, QuizAttempt::class, SurveyResponse::class] as $model) {
            $model::observe(GamificationObserver::class);
        }

        // Custom Blade directives for role checks
        Blade::if('role', function (string $role) {
            return auth()->check() && auth()->user()->hasRole($role);
        });

        Blade::if('anyrole', function (string $roles) {
            return auth()->check() && auth()->user()->hasAnyRole(explode(',', $roles));
        });
    }
}
