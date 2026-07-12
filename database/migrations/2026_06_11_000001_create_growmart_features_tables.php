<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('growmart_wishlist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('growmart_products')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'product_id']);
        });

        Schema::create('growmart_coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('type'); // percentage, fixed
            $table->integer('value'); // ngwee or percentage
            $table->integer('min_order_amount')->nullable();
            $table->integer('max_discount')->nullable();
            $table->integer('usage_limit')->nullable();
            $table->integer('used_count')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('growmart_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('growmart_products')->cascadeOnDelete();
            $table->tinyInteger('rating'); // 1-5
            $table->text('review_text')->nullable();
            $table->boolean('is_approved')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'product_id']);
        });

        Schema::table('growmart_orders', function (Blueprint $table) {
            $table->foreignId('coupon_id')->nullable()->constrained('growmart_coupons')->nullOnDelete()->after('id');
            $table->integer('discount')->default(0)->after('delivery_fee');
            $table->string('tracking_number')->nullable()->after('special_instructions');
            $table->string('tracking_url')->nullable()->after('tracking_number');
            $table->timestamp('estimated_delivery_at')->nullable()->after('tracking_url');
            $table->json('tracking_updates')->nullable()->after('estimated_delivery_at');
        });
    }

    public function down(): void
    {
        Schema::table('growmart_orders', function (Blueprint $table) {
            $table->dropColumn(['coupon_id', 'discount', 'tracking_number', 'tracking_url', 'estimated_delivery_at', 'tracking_updates']);
        });

        Schema::dropIfExists('growmart_reviews');
        Schema::dropIfExists('growmart_coupons');
        Schema::dropIfExists('growmart_wishlist_items');
    }
};
