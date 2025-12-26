<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add missing columns to marketplace_orders (only if they don't exist)
        Schema::table('marketplace_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('marketplace_orders', 'tracking_number')) {
                $table->string('tracking_number')->nullable()->after('tracking_info');
            }
            if (!Schema::hasColumn('marketplace_orders', 'courier_name')) {
                $table->string('courier_name')->nullable()->after('tracking_number');
            }
            if (!Schema::hasColumn('marketplace_orders', 'estimated_delivery')) {
                $table->timestamp('estimated_delivery')->nullable()->after('delivered_at');
            }
        });

        // Marketplace Reviews
        Schema::create('marketplace_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('marketplace_products')->onDelete('cascade');
            $table->foreignId('order_id')->constrained('marketplace_orders')->onDelete('cascade');
            $table->foreignId('buyer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('seller_id')->constrained('marketplace_sellers')->onDelete('cascade');
            $table->unsignedTinyInteger('rating'); // 1-5
            $table->text('comment')->nullable();
            $table->json('images')->nullable(); // Review images
            $table->boolean('is_verified_purchase')->default(true);
            $table->boolean('is_approved')->default(true);
            $table->unsignedInteger('helpful_count')->default(0);
            $table->unsignedInteger('not_helpful_count')->default(0);
            $table->text('seller_response')->nullable();
            $table->timestamp('seller_responded_at')->nullable();
            $table->timestamps();
            
            $table->index(['product_id', 'is_approved']);
            $table->index(['seller_id', 'rating']);
            $table->unique(['order_id', 'product_id', 'buyer_id']);
        });

        // Review Helpfulness Votes
        Schema::create('marketplace_review_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_id')->constrained('marketplace_reviews')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->boolean('is_helpful'); // true = helpful, false = not helpful
            $table->timestamps();
            
            $table->unique(['review_id', 'user_id']);
        });

        // Marketplace Disputes
        Schema::create('marketplace_disputes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('marketplace_orders')->onDelete('cascade');
            $table->foreignId('buyer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('seller_id')->constrained('marketplace_sellers')->onDelete('cascade');
            $table->enum('type', ['not_received', 'not_as_described', 'damaged', 'wrong_item', 'other']);
            $table->text('description');
            $table->json('evidence')->nullable(); // Photos, documents
            $table->enum('status', ['open', 'investigating', 'resolved', 'closed'])->default('open');
            $table->text('resolution')->nullable();
            $table->enum('resolution_type', ['refund', 'replacement', 'partial_refund', 'no_action'])->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            
            $table->index(['order_id', 'status']);
            $table->index(['seller_id', 'status']);
        });

        // Marketplace Wishlists
        Schema::create('marketplace_wishlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('marketplace_products')->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['user_id', 'product_id']);
        });

        // Marketplace Promotions
        Schema::create('marketplace_promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('marketplace_sellers')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['percentage', 'fixed_amount', 'buy_x_get_y', 'free_shipping']);
            $table->unsignedInteger('discount_value')->nullable(); // Percentage or amount in ngwee
            $table->unsignedInteger('min_purchase_amount')->nullable();
            $table->unsignedInteger('max_discount_amount')->nullable();
            $table->json('applicable_products')->nullable(); // Product IDs
            $table->json('applicable_categories')->nullable(); // Category IDs
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('usage_count')->default(0);
            $table->timestamps();
            
            $table->index(['seller_id', 'is_active']);
            $table->index(['starts_at', 'ends_at']);
        });

        // Marketplace Coupons
        Schema::create('marketplace_coupons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->nullable()->constrained('marketplace_sellers')->onDelete('cascade');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->enum('type', ['percentage', 'fixed_amount', 'free_shipping']);
            $table->unsignedInteger('discount_value');
            $table->unsignedInteger('min_purchase_amount')->nullable();
            $table->unsignedInteger('max_discount_amount')->nullable();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('usage_count')->default(0);
            $table->unsignedInteger('per_user_limit')->default(1);
            $table->timestamps();
            
            $table->index(['code', 'is_active']);
        });

        // Marketplace Coupon Usage
        Schema::create('marketplace_coupon_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained('marketplace_coupons')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('order_id')->constrained('marketplace_orders')->onDelete('cascade');
            $table->unsignedInteger('discount_amount');
            $table->timestamps();
            
            $table->index(['coupon_id', 'user_id']);
        });

        // Marketplace Messages (Buyer-Seller Communication)
        Schema::create('marketplace_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained('marketplace_orders')->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained('marketplace_products')->onDelete('cascade');
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('recipient_id')->constrained('users')->onDelete('cascade');
            $table->text('message');
            $table->json('attachments')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            
            $table->index(['sender_id', 'recipient_id']);
            $table->index(['order_id', 'created_at']);
        });

        // Marketplace Pickup Stations
        Schema::create('marketplace_pickup_stations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('province');
            $table->string('district');
            $table->text('address');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->json('operating_hours')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('capacity')->default(100);
            $table->timestamps();
            
            $table->index(['province', 'is_active']);
        });

        // Marketplace Payouts
        Schema::create('marketplace_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('marketplace_sellers')->onDelete('cascade');
            $table->unsignedBigInteger('amount'); // Amount in ngwee
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->enum('method', ['momo', 'airtel', 'zamtel', 'bank'])->default('momo');
            $table->string('account_number');
            $table->string('account_name');
            $table->string('reference_number')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->index(['seller_id', 'status']);
        });

        // Marketplace Transactions (Financial Ledger)
        Schema::create('marketplace_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->nullable()->constrained('marketplace_sellers')->onDelete('cascade');
            $table->foreignId('order_id')->nullable()->constrained('marketplace_orders')->onDelete('cascade');
            $table->foreignId('payout_id')->nullable()->constrained('marketplace_payouts')->onDelete('cascade');
            $table->enum('type', ['sale', 'commission', 'payout', 'refund', 'adjustment']);
            $table->bigInteger('amount'); // Can be negative for debits
            $table->bigInteger('balance_after');
            $table->text('description');
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['seller_id', 'created_at']);
            $table->index(['type', 'created_at']);
        });

        // Marketplace Product Views (Analytics)
        Schema::create('marketplace_product_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('marketplace_products')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->string('referrer')->nullable();
            $table->timestamps();
            
            $table->index(['product_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketplace_product_views');
        Schema::dropIfExists('marketplace_transactions');
        Schema::dropIfExists('marketplace_payouts');
        Schema::dropIfExists('marketplace_pickup_stations');
        Schema::dropIfExists('marketplace_messages');
        Schema::dropIfExists('marketplace_coupon_usage');
        Schema::dropIfExists('marketplace_coupons');
        Schema::dropIfExists('marketplace_promotions');
        Schema::dropIfExists('marketplace_wishlists');
        Schema::dropIfExists('marketplace_disputes');
        Schema::dropIfExists('marketplace_review_votes');
        Schema::dropIfExists('marketplace_reviews');
        
        Schema::table('marketplace_orders', function (Blueprint $table) {
            if (Schema::hasColumn('marketplace_orders', 'tracking_number')) {
                $table->dropColumn('tracking_number');
            }
            if (Schema::hasColumn('marketplace_orders', 'courier_name')) {
                $table->dropColumn('courier_name');
            }
            if (Schema::hasColumn('marketplace_orders', 'estimated_delivery')) {
                $table->dropColumn('estimated_delivery');
            }
        });
    }
};
