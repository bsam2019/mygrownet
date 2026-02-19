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
        if (!Schema::hasTable('social_shares')) {
            Schema::create('social_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('share_type'); // 'referral_link', 'content', 'platform_link'
            $table->string('platform')->nullable(); // 'facebook', 'twitter', 'whatsapp', 'copy_link'
            $table->text('shared_url')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('shared_at');
            $table->timestamps();

            // Indexes for performance
            $table->index(['user_id', 'shared_at']);
            $table->index('shared_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_shares');
    }
};
