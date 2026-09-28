<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('virtual_classrooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('platform')->default('zoom'); // zoom, teams, meet, webex, other
            $table->string('meeting_url');
            $table->string('meeting_id')->nullable();
            $table->string('passcode')->nullable();
            $table->string('host_name')->nullable();
            $table->timestamp('scheduled_at');
            $table->integer('duration_minutes')->default(60);
            $table->string('timezone')->default('Asia/Kuala_Lumpur');
            $table->string('status')->default('scheduled'); // scheduled, live, completed, cancelled
            $table->integer('max_participants')->nullable();
            $table->text('recording_url')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->string('recurrence_pattern')->nullable(); // daily, weekly, monthly
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'scheduled_at']);
            $table->index('course_id');
            $table->index('status');
        });

        Schema::create('virtual_classroom_attendees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('virtual_classroom_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('registered'); // registered, attended, absent
            $table->timestamp('joined_at')->nullable();
            $table->timestamp('left_at')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->timestamps();

            $table->unique(['virtual_classroom_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('virtual_classroom_attendees');
        Schema::dropIfExists('virtual_classrooms');
    }
};
