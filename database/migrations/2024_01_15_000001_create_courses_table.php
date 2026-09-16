<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Courses (global catalog, assigned to tenants)
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('objectives')->nullable(); // JSON array of learning objectives
            $table->string('category');              // e.g. "Phishing & Email Security"
            $table->string('difficulty')->default('beginner'); // beginner, intermediate, advanced
            $table->string('thumbnail_path')->nullable();
            $table->unsignedInteger('duration_minutes')->default(30);
            $table->unsignedInteger('passing_score')->default(70); // quiz pass %
            $table->boolean('is_active')->default(true);
            $table->boolean('is_mandatory')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('category');
            $table->index('is_active');
        });

        // Lessons within a course
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->longText('content'); // HTML content
            $table->string('content_type')->default('text'); // text, video, interactive
            $table->string('video_url')->nullable(); // YouTube/Vimeo embed URL
            $table->unsignedInteger('duration_minutes')->default(5);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['course_id', 'slug']);
            $table->index('sort_order');
        });

        // Quizzes (one per course, at end)
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('instructions')->nullable();
            $table->unsignedInteger('time_limit_minutes')->nullable(); // null = no limit
            $table->unsignedInteger('max_attempts')->default(3);       // 0 = unlimited
            $table->boolean('shuffle_questions')->default(true);
            $table->boolean('show_correct_answers')->default(true);    // after submission
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique('course_id'); // one quiz per course
        });

        // Quiz questions
        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $table->text('question');
            $table->string('question_type')->default('multiple_choice'); // multiple_choice, true_false, multi_select
            $table->text('explanation')->nullable(); // shown after answering
            $table->unsignedInteger('points')->default(1);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('quiz_id');
        });

        // Quiz answer options
        Schema::create('quiz_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('quiz_questions')->cascadeOnDelete();
            $table->text('answer_text');
            $table->boolean('is_correct')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('question_id');
        });

        // Course-tenant assignment (which tenants have access to which courses)
        Schema::create('course_tenant', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_mandatory')->default(false);
            $table->date('due_date')->nullable();
            $table->timestamps();

            $table->unique(['course_id', 'tenant_id']);
        });

        // Employee enrollment in courses
        Schema::create('course_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('not_started'); // not_started, in_progress, completed, failed
            $table->unsignedInteger('progress_percent')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->date('due_date')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'course_id']);
            $table->index(['tenant_id', 'status']);
        });

        // Lesson completion tracking
        Schema::create('lesson_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->timestamp('completed_at');
            $table->unsignedInteger('time_spent_seconds')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'lesson_id']);
        });

        // Quiz attempts
        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('score')->default(0);          // percentage
            $table->unsignedInteger('correct_count')->default(0);
            $table->unsignedInteger('total_questions')->default(0);
            $table->boolean('passed')->default(false);
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->unsignedInteger('time_spent_seconds')->default(0);
            $table->timestamps();

            $table->index(['user_id', 'quiz_id']);
        });

        // Individual question responses within an attempt
        Schema::create('quiz_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->constrained('quiz_attempts')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('quiz_questions')->cascadeOnDelete();
            $table->foreignId('answer_id')->nullable()->constrained('quiz_answers')->nullOnDelete();
            $table->json('selected_answer_ids')->nullable(); // for multi_select
            $table->boolean('is_correct')->default(false);
            $table->timestamps();

            $table->unique(['attempt_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_responses');
        Schema::dropIfExists('quiz_attempts');
        Schema::dropIfExists('lesson_completions');
        Schema::dropIfExists('course_enrollments');
        Schema::dropIfExists('course_tenant');
        Schema::dropIfExists('quiz_answers');
        Schema::dropIfExists('quiz_questions');
        Schema::dropIfExists('quizzes');
        Schema::dropIfExists('lessons');
        Schema::dropIfExists('courses');
    }
};
