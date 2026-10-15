<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('career_paths', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'is_active']);
        });

        Schema::create('career_path_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('career_path_id')->constrained('career_paths')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedInteger('stage_order')->default(0);
            $table->json('required_courses')->nullable();
            $table->json('required_certifications')->nullable();
            $table->unsignedInteger('estimated_duration_days')->nullable();
            $table->timestamps();

            $table->index(['career_path_id', 'stage_order']);
        });

        Schema::create('career_path_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('career_path_id')->constrained('career_paths')->cascadeOnDelete();
            $table->foreignId('current_stage_id')->nullable()->constrained('career_path_stages')->nullOnDelete();
            $table->timestamp('enrolled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'career_path_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('career_path_enrollments');
        Schema::dropIfExists('career_path_stages');
        Schema::dropIfExists('career_paths');
    }
};
