<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->longText('content');
            $table->string('version', 50)->default('1.0');
            $table->boolean('requires_acknowledgment')->default(true);
            $table->unsignedInteger('acknowledgment_deadline_days')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'is_published']);
        });

        Schema::create('policy_acknowledgments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('policy_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('version', 50);
            $table->timestamp('acknowledged_at');
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->unique(['policy_id', 'user_id', 'version']);
            $table->index(['tenant_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('policy_acknowledgments');
        Schema::dropIfExists('policies');
    }
};
