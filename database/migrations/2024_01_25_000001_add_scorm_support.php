<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->string('scorm_version', 10)->nullable()->after('content_type');
            $table->string('scorm_entry_point')->nullable()->after('scorm_version');
            $table->string('scorm_package_path')->nullable()->after('scorm_entry_point');
        });

        Schema::create('scorm_tracking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('tenant_id')->nullable();

            // SCORM 1.2 CMI data
            $table->string('lesson_status', 20)->default('not attempted');
            // incomplete, completed, passed, failed, browsed, not attempted
            $table->string('lesson_location')->nullable();
            $table->decimal('score_raw', 8, 2)->nullable();
            $table->decimal('score_min', 8, 2)->nullable();
            $table->decimal('score_max', 8, 2)->nullable();
            $table->integer('total_time_seconds')->default(0);
            $table->integer('session_time_seconds')->default(0);
            $table->text('suspend_data')->nullable();
            $table->string('exit_type', 20)->nullable(); // suspend, logout, time-out, ''
            $table->string('entry_type', 20)->nullable(); // ab-initio, resume, ''
            $table->json('cmi_data')->nullable(); // full CMI snapshot

            $table->timestamps();
            $table->unique(['user_id', 'lesson_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scorm_tracking');
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropColumn(['scorm_version', 'scorm_entry_point', 'scorm_package_path']);
        });
    }
};
