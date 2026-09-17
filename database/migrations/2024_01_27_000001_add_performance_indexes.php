<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_enrollments', function (Blueprint $table) {
            $table->index('due_date');
            $table->index('completed_at');
            $table->index(['status', 'due_date']);
            $table->index(['tenant_id', 'status']);
        });

        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->index('course_id');
            $table->index(['course_id', 'passed']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index(['tenant_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::table('course_enrollments', function (Blueprint $table) {
            $table->dropIndex(['due_date']);
            $table->dropIndex(['completed_at']);
            $table->dropIndex(['status', 'due_date']);
            $table->dropIndex(['tenant_id', 'status']);
        });

        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->dropIndex(['course_id']);
            $table->dropIndex(['course_id', 'passed']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'is_active']);
        });
    }
};
