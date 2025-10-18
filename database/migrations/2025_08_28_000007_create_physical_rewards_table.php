<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('physical_rewards', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->enum('category', ['electronics', 'property', 'vehicle', 'business_kit', 'merchandise']);
            $table->decimal('estimated_value', 10, 2);
            $table->json('required_membership_tiers'); // Which tiers are eligible
            $table->integer('required_referrals')->default(0);
            $table->decimal('required_subscription_amount', 10, 2)->default(0);
            $table->integer('required_sustained_months')->default(0);
            $table->integer('available_quantity')->default(1);
            $table->integer('allocated_quantity')->default(0);
            $table->string('image_url')->nullable();
            $table->json('specifications')->nullable();
            $table->text('terms_and_conditions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['category', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('physical_rewards');
    }
};