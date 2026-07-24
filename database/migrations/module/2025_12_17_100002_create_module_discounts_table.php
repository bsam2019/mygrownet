<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_discounts', function (Blueprint $table) {
            $table->id();
            $table->string('module_id', 50)->nullable();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->enum('discount_type', ['percentage', 'fixed'])->default('percentage');
            $table->decimal('discount_value', 10, 2);
            $table->enum('applies_to', ['all_tiers', 'specific_tiers', 'annual_only', 'monthly_only'])->default('all_tiers');
            $table->json('tier_keys')->nullable();
            $table->string('code', 50)->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->integer('max_uses')->nullable();
            $table->integer('current_uses')->default(0);
            $table->decimal('min_purchase_amount', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('module_id');
            $table->index('code');
            $table->index('is_active');
            $table->index(['starts_at', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_discounts');
    }
};
