<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_sso_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('provider', 30); // google, microsoft, okta
            $table->string('client_id');
            $table->text('client_secret'); // encrypted at model level
            $table->string('tenant_identifier')->nullable(); // Azure tenant ID, etc.
            $table->json('allowed_domains')->nullable(); // ["company.com", "company.org"]
            $table->boolean('is_active')->default(true);
            $table->boolean('auto_provision')->default(true); // auto-create users on first login
            $table->boolean('force_sso')->default(false); // disable password login
            $table->timestamps();

            $table->unique(['tenant_id', 'provider']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_sso_configs');
    }
};
