<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('growstream_creator_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            
            // Profile Info
            $table->string('display_name', 100);
            $table->text('bio')->nullable();
            $table->text('avatar_url')->nullable();
            $table->text('banner_url')->nullable();
            
            // Social Links
            $table->string('website_url')->nullable();
            $table->string('facebook_url')->nullable();
            $table->string('twitter_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('youtube_url')->nullable();
            
            // Status
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamp('verified_at')->nullable();
            $table->enum('creator_tier', ['bronze', 'silver', 'gold', 'platinum'])->default('bronze');
            
            // Statistics
            $table->integer('total_videos')->default(0);
            $table->bigInteger('total_views')->default(0);
            $table->integer('subscriber_count')->default(0);
            
            // Revenue (for future)
            $table->decimal('revenue_share_percentage', 5, 2)->default(70.00);
            $table->decimal('total_earnings', 15, 2)->default(0);
            $table->decimal('pending_payout', 15, 2)->default(0);
            
            // Upload Limits
            $table->boolean('can_upload')->default(true);
            $table->integer('upload_limit_per_month')->default(50);
            
            $table->timestamps();
            
            // Indexes
            $table->index('user_id');
            $table->index(['is_verified', 'is_active']);
            $table->index('creator_tier');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('growstream_creator_profiles');
    }
};
