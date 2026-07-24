<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bizboost_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('bizboost_businesses')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('objective'); // increase_sales, promote_stock, announce_discount, bring_back_customers, grow_followers
            $table->string('status')->default('draft'); // draft, scheduled, active, paused, completed, cancelled
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('duration_days')->default(7);
            $table->json('campaign_config')->nullable(); // sequence settings, timing, etc.
            $table->json('target_platforms')->nullable();
            $table->json('analytics')->nullable();
            $table->integer('posts_created')->default(0);
            $table->integer('posts_published')->default(0);
            $table->timestamps();
            
            $table->index(['business_id', 'status']);
            $table->index(['business_id', 'start_date']);
        });

        Schema::create('bizboost_campaign_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('bizboost_campaigns')->onDelete('cascade');
            $table->foreignId('post_id')->constrained('bizboost_posts')->onDelete('cascade');
            $table->integer('sequence_day'); // Day 1, Day 2, etc.
            $table->string('sequence_type')->nullable(); // intro, engagement, reminder, cta
            $table->timestamps();
            
            $table->index(['campaign_id', 'sequence_day']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bizboost_campaign_posts');
        Schema::dropIfExists('bizboost_campaigns');
    }
};
