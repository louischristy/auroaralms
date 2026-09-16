<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // course_enrollments: (user_id, course_id) already has a unique index
        // course_enrollments: (tenant_id, status) already has an index
        Schema::table('course_enrollments', function (Blueprint $table) {
            $table->index('status', 'ce_status_index');
        });

        Schema::table('lesson_completions', function (Blueprint $table) {
            $table->index(['user_id', 'course_id'], 'lc_user_course_index');
        });

        // quiz_attempts: (user_id, quiz_id) already has an index
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->index(['user_id', 'course_id'], 'qa_user_course_index');
        });

        Schema::table('certificates', function (Blueprint $table) {
            $table->index(['user_id', 'tenant_id'], 'cert_user_tenant_index');
        });

        Schema::table('phishing_results', function (Blueprint $table) {
            $table->index(['campaign_id', 'status'], 'pr_campaign_status_index');
        });

        Schema::table('user_badges', function (Blueprint $table) {
            $table->index('user_id', 'ub_user_index');
        });

        Schema::table('policy_acknowledgments', function (Blueprint $table) {
            $table->index(['user_id', 'policy_id'], 'pa_user_policy_index');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index(['tenant_id', 'is_active'], 'users_tenant_active_index');
        });
    }

    public function down(): void
    {
        Schema::table('course_enrollments', function (Blueprint $table) {
            $table->dropIndex('ce_status_index');
        });

        Schema::table('lesson_completions', function (Blueprint $table) {
            $table->dropIndex('lc_user_course_index');
        });

        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->dropIndex('qa_user_course_index');
        });

        Schema::table('certificates', function (Blueprint $table) {
            $table->dropIndex('cert_user_tenant_index');
        });

        Schema::table('phishing_results', function (Blueprint $table) {
            $table->dropIndex('pr_campaign_status_index');
        });

        Schema::table('user_badges', function (Blueprint $table) {
            $table->dropIndex('ub_user_index');
        });

        Schema::table('policy_acknowledgments', function (Blueprint $table) {
            $table->dropIndex('pa_user_policy_index');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_tenant_active_index');
        });
    }
};
