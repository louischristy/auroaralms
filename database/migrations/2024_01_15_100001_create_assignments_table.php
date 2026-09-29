<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('instructions')->nullable();
            $table->integer('due_days_after_enrollment')->nullable();
            $table->integer('max_file_size_mb')->default(10);
            $table->string('allowed_file_types')->default('pdf,doc,docx,ppt,pptx,xls,xlsx,zip');
            $table->boolean('is_mandatory')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['course_id', 'is_active']);
            $table->index('tenant_id');
        });

        Schema::create('assignment_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained('assignments')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('file_path');
            $table->string('file_name');
            $table->integer('file_size');
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'submitted', 'under_review', 'approved', 'rejected', 'resubmitted'])->default('submitted');
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reviewer_feedback')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->integer('grade')->nullable();
            $table->timestamp('submitted_at');
            $table->timestamps();

            $table->index(['assignment_id', 'user_id']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignment_submissions');
        Schema::dropIfExists('assignments');
    }
};
