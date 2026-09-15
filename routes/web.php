<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Platform\TenantController;
use App\Http\Controllers\Platform\PlatformSettingController;
use App\Http\Controllers\Platform\PlatformUserController;
use App\Http\Controllers\Client\UserController;
use App\Http\Controllers\Client\DepartmentController;
use App\Http\Controllers\Client\PolicyController;
use App\Http\Controllers\Employee\CourseController;
use App\Http\Controllers\Employee\PolicyAcknowledgmentController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// ── Public ──
Route::get('/', function () {
    return redirect()->route('login');
});

// ── Auth (Guest) ──
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
    Route::get('forgot-password', [ForgotPasswordController::class, 'showForm'])->name('password.request');
    Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLink'])->name('password.email');
    Route::get('reset-password/{token}', [ResetPasswordController::class, 'showForm'])->name('password.reset');
    Route::post('reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');
});

// ── Authenticated ──
Route::middleware(['auth', 'resolve.tenant', 'inject.branding'])->group(function () {
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // ── Platform Admin Routes ──
    Route::middleware('role:platform-admin')->prefix('platform')->name('platform.')->group(function () {
        // Tenants
        Route::resource('tenants', TenantController::class);
        Route::post('tenants/{tenant}/toggle-status', [TenantController::class, 'toggleStatus'])->name('tenants.toggle-status');

        // Platform Settings
        Route::get('settings', [PlatformSettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [PlatformSettingController::class, 'update'])->name('settings.update');

        // All Users (cross-tenant view)
        Route::get('users', [PlatformUserController::class, 'index'])->name('users.index');
    });

    // ── Client Admin Routes ──
    Route::middleware('role:platform-admin,client-admin')->prefix('manage')->name('manage.')->group(function () {
        Route::resource('users', UserController::class);
        Route::post('users/import', [UserController::class, 'import'])->name('users.import');
        Route::get('users/export', [UserController::class, 'export'])->name('users.export');
        Route::resource('departments', DepartmentController::class);
        Route::resource('policies', PolicyController::class);
        Route::post('policies/{policy}/push', [PolicyController::class, 'push'])->name('policies.push');
    });

    // ── Manager Routes ──
    Route::middleware('role:platform-admin,client-admin,manager')->prefix('team')->name('team.')->group(function () {
        Route::get('members', [UserController::class, 'teamMembers'])->name('members');
        Route::get('reports', [DashboardController::class, 'teamReports'])->name('reports');
    });

    // ── Employee Routes (all authenticated users) ──
    Route::prefix('learn')->name('learn.')->group(function () {
        Route::get('courses', [CourseController::class, 'index'])->name('courses.index');
        Route::get('courses/{course}', [CourseController::class, 'show'])->name('courses.show');
        Route::post('courses/{course}/complete-module', [CourseController::class, 'completeModule'])->name('courses.complete-module');

        Route::get('policies', [PolicyAcknowledgmentController::class, 'index'])->name('policies.index');
        Route::get('policies/{policy}', [PolicyAcknowledgmentController::class, 'show'])->name('policies.show');
        Route::post('policies/{policy}/acknowledge', [PolicyAcknowledgmentController::class, 'acknowledge'])->name('policies.acknowledge');
    });
});
