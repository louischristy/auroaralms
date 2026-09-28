<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('billing_cycle')->default('monthly'); // monthly, quarterly, yearly, custom
            $table->decimal('base_price', 10, 2)->default(0);
            $table->decimal('price_per_user', 10, 2)->default(0);
            $table->string('currency')->default('USD');
            $table->integer('min_users')->default(1);
            $table->integer('max_users')->nullable();
            $table->integer('max_courses')->nullable();
            $table->integer('max_storage_gb')->nullable();
            $table->json('features')->nullable(); // feature flags
            $table->json('modules')->nullable(); // enabled modules
            $table->boolean('custom_branding')->default(false);
            $table->boolean('custom_domain')->default(false);
            $table->boolean('api_access')->default(false);
            $table->boolean('sso_enabled')->default(false);
            $table->boolean('priority_support')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);
            $table->integer('trial_days')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('tenant_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('subscription_plans')->cascadeOnDelete();
            $table->string('status')->default('active'); // active, trial, suspended, cancelled, expired
            $table->decimal('custom_price', 10, 2)->nullable(); // override plan price
            $table->decimal('custom_price_per_user', 10, 2)->nullable();
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->string('discount_reason')->nullable();
            $table->string('billing_cycle')->nullable(); // override plan cycle
            $table->string('currency')->nullable(); // override currency
            $table->json('custom_features')->nullable(); // override features
            $table->json('custom_modules')->nullable(); // override modules
            $table->integer('custom_max_users')->nullable();
            $table->integer('custom_max_courses')->nullable();
            $table->integer('custom_max_storage_gb')->nullable();
            $table->timestamp('starts_at');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index('expires_at');
        });

        Schema::create('subscription_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_id')->constrained('tenant_subscriptions')->cascadeOnDelete();
            $table->string('invoice_number')->unique();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->string('currency')->default('USD');
            $table->string('status')->default('pending'); // pending, paid, overdue, cancelled, refunded
            $table->timestamp('issued_at');
            $table->timestamp('due_at');
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable();
            $table->json('line_items')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index('due_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_invoices');
        Schema::dropIfExists('tenant_subscriptions');
        Schema::dropIfExists('subscription_plans');
    }
};
