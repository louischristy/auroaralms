<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gamification_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('points');
            $table->string('action'); // course_completed, quiz_passed, lesson_completed, badge_earned, login_streak, survey_completed
            $table->string('reference_type')->nullable(); // Course, Quiz, Lesson, etc.
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'tenant_id']);
            $table->index('action');
        });

        Schema::create('user_streaks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('current_streak')->default(0);
            $table->integer('longest_streak')->default(0);
            $table->date('last_activity_date')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'tenant_id']);
        });

        // Add total_points to users for quick leaderboard queries
        Schema::table('users', function (Blueprint $table) {
            $table->integer('total_points')->default(0)->after('avatar_path');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('total_points');
        });
        Schema::dropIfExists('user_streaks');
        Schema::dropIfExists('gamification_points');
    }
};
