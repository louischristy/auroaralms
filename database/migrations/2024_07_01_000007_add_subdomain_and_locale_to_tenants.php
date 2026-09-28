<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            if (!Schema::hasColumn('tenants', 'subdomain')) {
                $table->string('subdomain')->nullable()->unique()->after('domain');
            }
            if (!Schema::hasColumn('tenants', 'locale')) {
                $table->string('locale')->default('en')->after('accent_color');
            }
            if (!Schema::hasColumn('tenants', 'timezone')) {
                $table->string('timezone')->default('Asia/Kuala_Lumpur')->after('locale');
            }
            if (!Schema::hasColumn('tenants', 'selected_certificate_template_id')) {
                $table->foreignId('selected_certificate_template_id')->nullable()->after('subscription_expires_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['subdomain', 'locale', 'timezone', 'selected_certificate_template_id']);
        });
    }
};
