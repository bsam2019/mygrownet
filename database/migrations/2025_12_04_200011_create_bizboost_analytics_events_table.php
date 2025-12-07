<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add indexes to existing analytics_events table if they don't exist
        if (Schema::hasTable('bizboost_analytics_events')) {
            // Try to add indexes, ignore if they already exist
            try {
                Schema::table('bizboost_analytics_events', function (Blueprint $table) {
                    $table->index(['business_id', 'event_type', 'recorded_at'], 'bb_analytics_events_biz_type_date');
                });
            } catch (\Exception $e) {
                // Index already exists, ignore
            }
            
            try {
                Schema::table('bizboost_analytics_events', function (Blueprint $table) {
                    $table->index(['business_id', 'recorded_at'], 'bb_analytics_events_biz_date');
                });
            } catch (\Exception $e) {
                // Index already exists, ignore
            }
            
            try {
                Schema::table('bizboost_analytics_events', function (Blueprint $table) {
                    $table->index(['post_id', 'event_type'], 'bb_analytics_events_post_type');
                });
            } catch (\Exception $e) {
                // Index already exists, ignore
            }
        } else {
            Schema::create('bizboost_analytics_events', function (Blueprint $table) {
                $table->id();
                $table->foreignId('business_id')->constrained('bizboost_businesses')->onDelete('cascade');
                $table->string('event_type'); // page_view, post_engagement, link_click, etc.
                $table->string('source')->nullable(); // facebook, instagram, mini_website, qr_code
                $table->foreignId('post_id')->nullable();
                $table->json('payload')->nullable();
                $table->string('ip_address')->nullable();
                $table->string('user_agent')->nullable();
                $table->string('referrer')->nullable();
                $table->timestamp('recorded_at');
                $table->timestamps();
                
                $table->index(['business_id', 'event_type', 'recorded_at'], 'bb_analytics_events_biz_type_date');
                $table->index(['business_id', 'recorded_at'], 'bb_analytics_events_biz_date');
                $table->index(['post_id', 'event_type'], 'bb_analytics_events_post_type');
            });
        }

        // Aggregated analytics for faster queries
        if (!Schema::hasTable('bizboost_analytics_daily')) {
            Schema::create('bizboost_analytics_daily', function (Blueprint $table) {
                $table->id();
                $table->foreignId('business_id')->constrained('bizboost_businesses')->onDelete('cascade');
                $table->date('date');
                $table->integer('page_views')->default(0);
                $table->integer('unique_visitors')->default(0);
                $table->integer('post_impressions')->default(0);
                $table->integer('post_engagements')->default(0);
                $table->integer('link_clicks')->default(0);
                $table->integer('whatsapp_clicks')->default(0);
                $table->integer('qr_scans')->default(0);
                $table->json('top_posts')->nullable();
                $table->json('traffic_sources')->nullable();
                $table->timestamps();
                
                $table->unique(['business_id', 'date']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('bizboost_analytics_daily');
        Schema::dropIfExists('bizboost_analytics_events');
    }
};
