<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('two_factor_secret')->nullable()->after('password');
            $table->boolean('two_factor_enabled')->default(false)->after('two_factor_secret');
            $table->text('two_factor_recovery_codes')->nullable()->after('two_factor_enabled');
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->boolean('require_two_factor')->default(false)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['two_factor_secret', 'two_factor_enabled', 'two_factor_recovery_codes']);
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('require_two_factor');
        });
    }
};
