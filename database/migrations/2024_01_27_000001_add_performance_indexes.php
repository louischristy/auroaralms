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
        });

        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->index(['course_id', 'passed']);
        });
    }

    public function down(): void
    {
        Schema::table('course_enrollments', function (Blueprint $table) {
            $table->dropIndex(['due_date']);
            $table->dropIndex(['completed_at']);
            $table->dropIndex(['status', 'due_date']);
        });

        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->dropIndex(['course_id', 'passed']);
        });
    }
};
