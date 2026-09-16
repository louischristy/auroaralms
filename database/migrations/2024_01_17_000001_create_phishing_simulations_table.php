<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('phishing_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('scenario_type', ['credential_harvest', 'malware_link', 'attachment', 'urgent_action']);
            $table->string('subject');
            $table->string('sender_name');
            $table->string('sender_email');
            $table->longText('body_html');
            $table->longText('landing_page_html')->nullable();
            $table->enum('difficulty', ['easy', 'medium', 'hard']);
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        Schema::create('phishing_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('template_id')->constrained('phishing_templates')->cascadeOnDelete();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->enum('status', ['draft', 'scheduled', 'active', 'completed'])->default('draft');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('phishing_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('phishing_campaigns')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->timestamp('email_sent_at')->nullable();
            $table->timestamp('email_opened_at')->nullable();
            $table->timestamp('link_clicked_at')->nullable();
            $table->timestamp('data_submitted_at')->nullable();
            $table->timestamp('reported_at')->nullable();
            $table->enum('status', ['sent', 'opened', 'clicked', 'submitted', 'reported'])->default('sent');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phishing_results');
        Schema::dropIfExists('phishing_campaigns');
        Schema::dropIfExists('phishing_templates');
    }
};
