<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('phishing_campaigns', function (Blueprint $table) {
            $table->json('target_department_ids')->nullable()->after('template_id');
            $table->string('target_type', 20)->default('all')->after('template_id'); // all or departments
            $table->unsignedTinyInteger('custom_click_rate')->nullable()->after('status');
            $table->unsignedTinyInteger('custom_report_rate')->nullable()->after('custom_click_rate');
            $table->boolean('training_aware')->default(true)->after('custom_report_rate');
        });
    }

    public function down(): void
    {
        Schema::table('phishing_campaigns', function (Blueprint $table) {
            $table->dropColumn(['target_type', 'target_department_ids', 'custom_click_rate', 'custom_report_rate', 'training_aware']);
        });
    }
};
