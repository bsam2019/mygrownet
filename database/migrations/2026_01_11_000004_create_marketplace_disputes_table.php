<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketplace_disputes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('marketplace_orders')->onDelete('cascade');
            $table->foreignId('buyer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('seller_id')->constrained('marketplace_sellers')->onDelete('cascade');
            $table->string('type'); // e.g., 'product_not_received', 'wrong_item', 'damaged', 'refund_request'
            $table->text('description');
            $table->json('evidence')->nullable(); // images, documents
            $table->enum('status', ['open', 'investigating', 'resolved', 'closed'])->default('open');
            $table->enum('resolution_type', ['refund', 'replacement', 'partial_refund', 'no_action'])->nullable();
            $table->text('resolution')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            
            $table->index('order_id');
            $table->index('buyer_id');
            $table->index('seller_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_disputes');
    }
};
