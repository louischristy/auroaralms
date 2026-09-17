<?php

use App\Http\Controllers\Api\ScormApiController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\SsoController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Platform\AuditLogController;
use App\Http\Controllers\Platform\TenantController;
use App\Http\Controllers\Platform\PlatformSettingController;
use App\Http\Controllers\Platform\PlatformUserController;
use App\Http\Controllers\Platform\CourseManagementController;
use App\Http\Controllers\Platform\CourseCategoryController;
use App\Http\Controllers\Platform\ReportController as PlatformReportController;
use App\Http\Controllers\Platform\TenantSsoController;
use App\Http\Controllers\Client\ReportController as ClientReportController;
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
use App\Http\Controllers\SessionController;
use Illuminate\Support\Facades\Route;

// ── Public ──
Route::get('/', function () {
    return redirect()->route('login');
});

// ── Auth (Guest) ──
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
    Route::post('login/check-sso', [LoginController::class, 'checkSso'])->name('login.check-sso');
    Route::get('forgot-password', [ForgotPasswordController::class, 'showForm'])->name('password.request');
    Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLink'])->name('password.email');
    Route::get('reset-password/{token}', [ResetPasswordController::class, 'showForm'])->name('password.reset');
    Route::post('reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');

    // SSO
    Route::get('sso/{provider}/redirect', [SsoController::class, 'redirect'])->name('sso.redirect');
    Route::get('sso/{provider}/callback', [SsoController::class, 'callback'])->name('sso.callback');

    // 2FA Challenge (user is not yet fully authenticated)
    Route::get('two-factor/challenge', [TwoFactorController::class, 'challenge'])->name('two-factor.challenge')->withoutMiddleware('guest');
    Route::post('two-factor/verify', [TwoFactorController::class, 'verifyChallenge'])->name('two-factor.verify')->withoutMiddleware('guest');
});

// ── Authenticated ──
Route::middleware(['auth', 'resolve.tenant', 'inject.branding'])->group(function () {
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');

    // 2FA Setup (must be accessible before 2fa.verified for forced setup)
    Route::get('two-factor/setup', [TwoFactorController::class, 'setup'])->name('two-factor.setup');
    Route::post('two-factor/confirm', [TwoFactorController::class, 'confirmSetup'])->name('two-factor.confirm');
    Route::post('two-factor/disable', [TwoFactorController::class, 'disable'])->name('two-factor.disable');
    Route::post('two-factor/recovery-codes', [TwoFactorController::class, 'regenerateRecoveryCodes'])->name('two-factor.recovery-codes');
});

Route::middleware(['auth', 'resolve.tenant', 'inject.branding', '2fa.verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Notifications
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');

    // Profile
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Active Sessions
    Route::get('sessions', [SessionController::class, 'index'])->name('sessions.index');
    Route::delete('sessions/{session}', [SessionController::class, 'destroy'])->name('sessions.destroy');
    Route::delete('sessions', [SessionController::class, 'destroyAll'])->name('sessions.destroy-all');

    // ── Platform Admin Routes ──
    Route::middleware('role:platform-admin')->prefix('platform')->name('platform.')->group(function () {
        // Tenants
        Route::resource('tenants', TenantController::class);
        Route::post('tenants/{tenant}/toggle-status', [TenantController::class, 'toggleStatus'])->name('tenants.toggle-status');

        // Tenant SSO Settings
        Route::get('tenants/{tenant}/sso', [TenantSsoController::class, 'index'])->name('tenants.sso.index');
        Route::post('tenants/{tenant}/sso', [TenantSsoController::class, 'store'])->name('tenants.sso.store');
        Route::put('tenants/{tenant}/sso/{sso}', [TenantSsoController::class, 'update'])->name('tenants.sso.update');
        Route::delete('tenants/{tenant}/sso/{sso}', [TenantSsoController::class, 'destroy'])->name('tenants.sso.destroy');

        // Platform Settings
        Route::get('settings', [PlatformSettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [PlatformSettingController::class, 'update'])->name('settings.update');

        // All Users (cross-tenant view)
        Route::get('users', [PlatformUserController::class, 'index'])->name('users.index');
        Route::post('users/{user}/force-logout', [SessionController::class, 'forceLogout'])->name('users.force-logout');

        // Reports & Analytics
        Route::get('reports', [PlatformReportController::class, 'index'])->name('reports.index');
        Route::get('reports/tenant-completion', [PlatformReportController::class, 'tenantCompletion'])->name('reports.tenant-completion');
        Route::get('reports/course-performance', [PlatformReportController::class, 'coursePerformance'])->name('reports.course-performance');
        Route::get('reports/quiz-analytics', [PlatformReportController::class, 'quizAnalytics'])->name('reports.quiz-analytics');

        // Audit Logs
        Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('audit-logs/{auditLog}', [AuditLogController::class, 'show'])->name('audit-logs.show');

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
        Route::post('users/{user}/force-logout', [SessionController::class, 'forceLogout'])->name('users.force-logout');
        Route::post('users/import', [UserController::class, 'import'])->name('users.import');
        Route::get('users/export', [UserController::class, 'export'])->name('users.export');
        Route::resource('departments', DepartmentController::class);
        Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('audit-logs/{auditLog}', [AuditLogController::class, 'show'])->name('audit-logs.show');

        Route::resource('policies', PolicyController::class);
        Route::post('policies/{policy}/push', [PolicyController::class, 'push'])->name('policies.push');

        Route::resource('phishing', PhishingController::class)->except(['edit', 'update', 'destroy']);
        Route::post('phishing/{campaign}/simulate', [PhishingController::class, 'simulate'])->name('phishing.simulate');

        // Reports
        Route::get('reports', [ClientReportController::class, 'index'])->name('reports.index');
        Route::get('reports/user-progress', [ClientReportController::class, 'userProgress'])->name('reports.user-progress');
        Route::get('reports/department-breakdown', [ClientReportController::class, 'departmentBreakdown'])->name('reports.department-breakdown');
        Route::get('reports/overdue-training', [ClientReportController::class, 'overdueTraining'])->name('reports.overdue-training');

        // Client Course Builder
        Route::resource('courses', ClientCourseController::class);
        Route::post('courses/{course}/lessons', [ClientCourseController::class, 'storeLesson'])->name('courses.lessons.store');
        Route::put('courses/{course}/lessons/{lesson}', [ClientCourseController::class, 'updateLesson'])->name('courses.lessons.update');
        Route::delete('courses/{course}/lessons/{lesson}', [ClientCourseController::class, 'destroyLesson'])->name('courses.lessons.destroy');
        Route::post('courses/{course}/quiz', [ClientCourseController::class, 'storeQuiz'])->name('courses.quiz.store');
        Route::post('courses/{course}/questions', [ClientCourseController::class, 'storeQuestion'])->name('courses.questions.store');
        Route::delete('courses/{course}/questions/{question}', [ClientCourseController::class, 'destroyQuestion'])->name('courses.questions.destroy');
        Route::put('courses/{course}/questions/{question}', [ClientCourseController::class, 'updateQuestion'])->name('courses.questions.update');
        Route::post('courses/{course}/lessons/reorder', [ClientCourseController::class, 'reorderLessons'])->name('courses.lessons.reorder');
        Route::get('courses/{course}/preview', [ClientCourseController::class, 'preview'])->name('courses.preview');
    });

    // ── Manager Routes ──
    Route::middleware('role:platform-admin,client-admin,manager')->prefix('team')->name('team.')->group(function () {
        Route::get('members', [UserController::class, 'teamMembers'])->name('members');
        Route::get('reports', [DashboardController::class, 'teamReports'])->name('reports');
    });

    // ── SCORM API (authenticated, CSRF-protected) ──
    Route::post('api/scorm/{lesson}/initialize', [ScormApiController::class, 'initialize'])->name('scorm.initialize');
    Route::post('api/scorm/{lesson}/commit', [ScormApiController::class, 'commit'])->name('scorm.commit');
    Route::post('api/scorm/{lesson}/finish', [ScormApiController::class, 'finish'])->name('scorm.finish');

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
