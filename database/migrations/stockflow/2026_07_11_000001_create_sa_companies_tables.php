<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sa_companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('currency', 10)->default('ZMW');
            $table->string('logo_path')->nullable();
            $table->enum('status', ['active', 'suspended', 'cancelled'])->default('active');
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        Schema::create('sa_subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price_monthly', 12, 2)->default(0);
            $table->decimal('price_yearly', 12, 2)->default(0);
            $table->integer('max_companies')->nullable();
            $table->integer('max_items_per_audit')->nullable();
            $table->json('features')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('sa_company_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sa_company_id')->constrained('sa_companies')->cascadeOnDelete();
            $table->foreignId('sa_subscription_plan_id')->constrained('sa_subscription_plans')->cascadeOnDelete();
            $table->enum('status', ['trial', 'active', 'past_due', 'cancelled', 'expired'])->default('trial');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('next_billing_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sa_company_subscriptions');
        Schema::dropIfExists('sa_subscription_plans');
        Schema::dropIfExists('sa_companies');
    }
};
