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
        if (!Schema::hasTable('promotional_cards')) {
            Schema::create('promotional_cards', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('image_path'); // Main promotional image (OG image)
            $table->string('thumbnail_path')->nullable(); // Optional thumbnail
            $table->string('category')->default('general'); // general, opportunity, training, success
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            
            // OG Meta tags for social sharing
            $table->string('og_title')->nullable();
            $table->text('og_description')->nullable();
            $table->string('og_image')->nullable(); // Can be different from main image
            
            // Share tracking
            $table->integer('share_count')->default(0);
            $table->integer('view_count')->default(0);
            
            // Admin info
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['is_active', 'sort_order']);
            $table->index('category');
            $table->index('created_at');
        });

        // Track individual user shares
        if (!Schema::hasTable('promotional_card_shares')) {
            Schema::create('promotional_card_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotional_card_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('platform'); // facebook, whatsapp, twitter, etc.
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('shared_at');
            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'shared_at']);
            $table->index(['promotional_card_id', 'shared_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotional_card_shares');
        Schema::dropIfExists('promotional_cards');
    }
};
