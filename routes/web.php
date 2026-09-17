<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Platform\TenantController;
use App\Http\Controllers\Platform\PlatformSettingController;
use App\Http\Controllers\Platform\PlatformUserController;
use App\Http\Controllers\Platform\CourseManagementController;
use App\Http\Controllers\Platform\CourseCategoryController;
use App\Http\Controllers\Client\UserController;
use App\Http\Controllers\Client\DepartmentController;
use App\Http\Controllers\Client\ClientCourseController;
use App\Http\Controllers\Client\PhishingController;
use App\Http\Controllers\Client\PolicyController;
use App\Http\Controllers\Employee\CertificateController;
use App\Http\Controllers\Employee\CourseController;
use App\Http\Controllers\Employee\LeaderboardController;
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

        // Course Categories
        Route::get('categories', [CourseCategoryController::class, 'index'])->name('categories.index');
        Route::post('categories', [CourseCategoryController::class, 'store'])->name('categories.store');
        Route::put('categories/{category}', [CourseCategoryController::class, 'update'])->name('categories.update');
        Route::delete('categories/{category}', [CourseCategoryController::class, 'destroy'])->name('categories.destroy');

        // Course Management (catalog)
        Route::resource('courses', CourseManagementController::class);
        Route::post('courses/{course}/assign-tenants', [CourseManagementController::class, 'assignTenants'])->name('courses.assign-tenants');
        Route::post('courses/{course}/lessons', [CourseManagementController::class, 'storeLesson'])->name('courses.lessons.store');
        Route::put('courses/{course}/lessons/{lesson}', [CourseManagementController::class, 'updateLesson'])->name('courses.lessons.update');
        Route::delete('courses/{course}/lessons/{lesson}', [CourseManagementController::class, 'destroyLesson'])->name('courses.lessons.destroy');
        Route::post('courses/{course}/lessons/reorder', [CourseManagementController::class, 'reorderLessons'])->name('courses.lessons.reorder');
        Route::post('courses/{course}/quiz', [CourseManagementController::class, 'storeQuiz'])->name('courses.quiz.store');
        Route::post('courses/{course}/questions', [CourseManagementController::class, 'storeQuestion'])->name('courses.questions.store');
        Route::delete('courses/{course}/questions/{question}', [CourseManagementController::class, 'destroyQuestion'])->name('courses.questions.destroy');
    });

    // ── Client Admin Routes ──
    Route::middleware('role:platform-admin,client-admin')->prefix('manage')->name('manage.')->group(function () {
        Route::resource('users', UserController::class);
        Route::post('users/import', [UserController::class, 'import'])->name('users.import');
        Route::get('users/export', [UserController::class, 'export'])->name('users.export');
        Route::resource('departments', DepartmentController::class);
        Route::resource('policies', PolicyController::class);
        Route::post('policies/{policy}/push', [PolicyController::class, 'push'])->name('policies.push');

        Route::resource('phishing', PhishingController::class)->except(['edit', 'update', 'destroy']);
        Route::post('phishing/{campaign}/simulate', [PhishingController::class, 'simulate'])->name('phishing.simulate');

        // Client Course Builder
        Route::resource('courses', ClientCourseController::class);
        Route::post('courses/{course}/lessons', [ClientCourseController::class, 'storeLesson'])->name('courses.lessons.store');
        Route::put('courses/{course}/lessons/{lesson}', [ClientCourseController::class, 'updateLesson'])->name('courses.lessons.update');
        Route::delete('courses/{course}/lessons/{lesson}', [ClientCourseController::class, 'destroyLesson'])->name('courses.lessons.destroy');
        Route::post('courses/{course}/quiz', [ClientCourseController::class, 'storeQuiz'])->name('courses.quiz.store');
        Route::post('courses/{course}/questions', [ClientCourseController::class, 'storeQuestion'])->name('courses.questions.store');
        Route::delete('courses/{course}/questions/{question}', [ClientCourseController::class, 'destroyQuestion'])->name('courses.questions.destroy');
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
        Route::get('courses/{course}/lessons/{lesson}', [CourseController::class, 'lesson'])->name('courses.lesson');
        Route::post('courses/{course}/lessons/{lesson}/complete', [CourseController::class, 'completeLesson'])->name('courses.complete-lesson');
        Route::get('courses/{course}/quiz', [CourseController::class, 'showQuiz'])->name('courses.quiz');
        Route::post('courses/{course}/quiz', [CourseController::class, 'submitQuiz'])->name('courses.submit-quiz');
        Route::get('courses/{course}/quiz-result/{attempt}', [CourseController::class, 'quizResult'])->name('courses.quiz-result');

        Route::get('certificates', [CertificateController::class, 'index'])->name('certificates.index');
        Route::get('certificates/{certificate}/download', [CertificateController::class, 'download'])->name('certificates.download');

        Route::get('policies', [PolicyAcknowledgmentController::class, 'index'])->name('policies.index');
        Route::get('policies/{policy}', [PolicyAcknowledgmentController::class, 'show'])->name('policies.show');
        Route::post('policies/{policy}/acknowledge', [PolicyAcknowledgmentController::class, 'acknowledge'])->name('policies.acknowledge');

        Route::get('leaderboard', [LeaderboardController::class, 'index'])->name('leaderboard.index');
    });
});
