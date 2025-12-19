<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Sellers
        Schema::create('marketplace_sellers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('business_name');
            $table->enum('business_type', ['individual', 'registered'])->default('individual');
            $table->string('province');
            $table->string('district');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('description')->nullable();
            $table->string('logo_path')->nullable();
            $table->enum('trust_level', ['new', 'verified', 'trusted', 'top'])->default('new');
            $table->enum('kyc_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->json('kyc_documents')->nullable();
            $table->text('kyc_rejection_reason')->nullable();
            $table->unsignedInteger('total_orders')->default(0);
            $table->decimal('rating', 3, 2)->default(0);
            $table->boolean('is_active')->default(false);
            $table->timestamps();
            
            $table->unique('user_id');
            $table->index(['province', 'is_active']);
            $table->index(['trust_level', 'is_active']);
        });

        // Categories
        Schema::create('marketplace_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Products
        Schema::create('marketplace_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('marketplace_sellers')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('marketplace_categories')->onDelete('restrict');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->unsignedInteger('price'); // In ngwee (cents)
            $table->unsignedInteger('compare_price')->nullable();
            $table->unsignedInteger('stock_quantity')->default(0);
            $table->json('images')->nullable();
            $table->enum('status', ['draft', 'pending', 'active', 'rejected', 'suspended'])->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('views')->default(0);
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            
            $table->index(['seller_id', 'status']);
            $table->index(['category_id', 'status']);
            $table->index(['status', 'stock_quantity']);
        });

        // Orders
        Schema::create('marketplace_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('buyer_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('seller_id')->constrained('marketplace_sellers')->onDelete('restrict');
            $table->enum('status', [
                'pending', 'paid', 'processing', 'shipped', 
                'delivered', 'completed', 'cancelled', 'disputed', 'refunded'
            ])->default('pending');
            $table->unsignedInteger('subtotal');
            $table->unsignedInteger('delivery_fee')->default(0);
            $table->unsignedInteger('total');
            $table->enum('delivery_method', ['self', 'courier', 'pickup'])->default('self');
            $table->json('delivery_address');
            $table->text('delivery_notes')->nullable();
            $table->string('payment_reference')->nullable();
            $table->text('tracking_info')->nullable();
            $table->string('delivery_proof')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->string('cancelled_by')->nullable();
            $table->text('dispute_reason')->nullable();
            $table->text('dispute_resolution')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('disputed_at')->nullable();
            $table->timestamps();
            
            $table->index(['buyer_id', 'status']);
            $table->index(['seller_id', 'status']);
            $table->index(['status', 'delivered_at']);
        });

        // Order Items
        Schema::create('marketplace_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('marketplace_orders')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('marketplace_products')->onDelete('restrict');
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('unit_price');
            $table->unsignedInteger('total_price');
            $table->timestamps();
        });

        // Escrow
        Schema::create('marketplace_escrow', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('marketplace_orders')->onDelete('cascade');
            $table->unsignedInteger('amount');
            $table->enum('status', ['held', 'released', 'refunded', 'disputed'])->default('held');
            $table->timestamp('held_at')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->text('release_reason')->nullable();
            $table->timestamps();
            
            $table->unique('order_id');
            $table->index('status');
        });

        // Reviews
        Schema::create('marketplace_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('marketplace_orders')->onDelete('cascade');
            $table->foreignId('buyer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('seller_id')->constrained('marketplace_sellers')->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained('marketplace_products')->onDelete('set null');
            $table->unsignedTinyInteger('rating');
            $table->text('comment')->nullable();
            $table->text('seller_response')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
            
            $table->unique('order_id');
            $table->index(['seller_id', 'rating']);
        });

        // Seller Balances (simplified for MVP)
        Schema::create('marketplace_seller_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('marketplace_sellers')->onDelete('cascade');
            $table->unsignedBigInteger('available_balance')->default(0);
            $table->unsignedBigInteger('pending_balance')->default(0);
            $table->unsignedBigInteger('total_earned')->default(0);
            $table->timestamps();
            
            $table->unique('seller_id');
        });

        // Buyer Refunds (simplified for MVP)
        Schema::create('marketplace_buyer_refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buyer_id')->constrained('users')->onDelete('cascade');
            $table->unsignedInteger('amount');
            $table->enum('status', ['pending', 'processed'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_buyer_refunds');
        Schema::dropIfExists('marketplace_seller_balances');
        Schema::dropIfExists('marketplace_reviews');
        Schema::dropIfExists('marketplace_escrow');
        Schema::dropIfExists('marketplace_order_items');
        Schema::dropIfExists('marketplace_orders');
        Schema::dropIfExists('marketplace_products');
        Schema::dropIfExists('marketplace_categories');
        Schema::dropIfExists('marketplace_sellers');
    }
};
