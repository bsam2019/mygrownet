<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_special_offers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->enum('offer_type', ['bundle', 'upgrade', 'trial_extension', 'bonus_feature'])->default('bundle');
            $table->json('module_ids');
            $table->string('tier_key', 50)->nullable();
            $table->decimal('original_price', 10, 2);
            $table->decimal('offer_price', 10, 2);
            $table->string('savings_display', 50)->nullable();
            $table->enum('billing_cycle', ['monthly', 'annual', 'one_time'])->default('annual');
            $table->json('bonus_features')->nullable();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->integer('max_purchases')->nullable();
            $table->integer('current_purchases')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('is_active');
            $table->index('is_featured');
            $table->index(['starts_at', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_special_offers');
    }
};
