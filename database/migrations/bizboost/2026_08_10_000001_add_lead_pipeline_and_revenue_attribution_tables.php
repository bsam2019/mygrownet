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
        // 1. Extend bizboost_customers table
        if (Schema::hasTable('bizboost_customers')) {
            Schema::table('bizboost_customers', function (Blueprint $table) {
                if (!Schema::hasColumn('bizboost_customers', 'intent_score')) {
                    $table->integer('intent_score')->default(0)->after('notes');
                }
                if (!Schema::hasColumn('bizboost_customers', 'intent_tier')) {
                    $table->string('intent_tier')->default('low')->after('intent_score'); // low, interested, hot, high_intent
                }
                if (!Schema::hasColumn('bizboost_customers', 'clv_zmw')) {
                    $table->decimal('clv_zmw', 12, 2)->default(0)->after('intent_tier');
                }
                if (!Schema::hasColumn('bizboost_customers', 'canonical_organization_id')) {
                    $table->unsignedBigInteger('canonical_organization_id')->nullable()->after('business_id');
                }
            });
        }

        // 2. Extend bizboost_campaigns table
        if (Schema::hasTable('bizboost_campaigns')) {
            Schema::table('bizboost_campaigns', function (Blueprint $table) {
                if (!Schema::hasColumn('bizboost_campaigns', 'spend_zmw')) {
                    $table->decimal('spend_zmw', 12, 2)->default(0)->after('objective');
                }
                if (!Schema::hasColumn('bizboost_campaigns', 'attributed_revenue_zmw')) {
                    $table->decimal('attributed_revenue_zmw', 12, 2)->default(0)->after('spend_zmw');
                }
                if (!Schema::hasColumn('bizboost_campaigns', 'marketing_roi_ratio')) {
                    $table->decimal('marketing_roi_ratio', 8, 2)->default(0)->after('attributed_revenue_zmw');
                }
            });
        }

        // 3. Create bizboost_lead_pipelines
        if (!Schema::hasTable('bizboost_lead_pipelines')) {
            Schema::create('bizboost_lead_pipelines', function (Blueprint $table) {
                $table->id();
                $table->foreignId('business_id')->constrained('bizboost_businesses')->onDelete('cascade');
                $table->string('name'); // e.g. Sales Pipeline, School Admissions, Real Estate Leads
                $table->string('slug');
                $table->boolean('is_default')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['business_id', 'slug']);
            });
        }

        // 4. Create bizboost_pipeline_stages
        if (!Schema::hasTable('bizboost_pipeline_stages')) {
            Schema::create('bizboost_pipeline_stages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pipeline_id')->constrained('bizboost_lead_pipelines')->onDelete('cascade');
                $table->string('name'); // e.g. New, Contacted, Qualified, Quotation, Won, Lost
                $table->string('color')->default('#6366f1');
                $table->integer('sort_order')->default(0);
                $table->boolean('is_won')->default(false);
                $table->boolean('is_lost')->default(false);
                $table->integer('sla_target_minutes')->default(30); // SLA response target
                $table->timestamps();
            });
        }

        // 5. Create bizboost_leads
        if (!Schema::hasTable('bizboost_leads')) {
            Schema::create('bizboost_leads', function (Blueprint $table) {
                $table->id();
                $table->foreignId('business_id')->constrained('bizboost_businesses')->onDelete('cascade');
                $table->foreignId('customer_id')->nullable()->constrained('bizboost_customers')->onDelete('cascade');
                $table->foreignId('pipeline_id')->constrained('bizboost_lead_pipelines')->onDelete('cascade');
                $table->foreignId('stage_id')->constrained('bizboost_pipeline_stages')->onDelete('cascade');
                $table->foreignId('assigned_user_id')->nullable()->constrained('users')->onDelete('set null');
                
                $table->string('title');
                $table->string('source')->default('website'); // website, smart_form, ai_chat, whatsapp, phone, ads, referral, manual
                $table->decimal('estimated_value_zmw', 12, 2)->default(0);
                $table->integer('intent_score')->default(0);
                
                $table->timestamp('first_response_at')->nullable();
                $table->timestamp('stage_changed_at')->nullable();
                $table->timestamp('won_lost_at')->nullable();
                $table->text('loss_reason')->nullable();
                $table->json('custom_attributes')->nullable();
                
                $table->timestamps();

                $table->index(['business_id', 'stage_id']);
                $table->index(['business_id', 'source']);
            });
        }

        // 6. Create bizboost_trackable_links
        if (!Schema::hasTable('bizboost_trackable_links')) {
            Schema::create('bizboost_trackable_links', function (Blueprint $table) {
                $table->id();
                $table->foreignId('business_id')->constrained('bizboost_businesses')->onDelete('cascade');
                $table->foreignId('campaign_id')->nullable()->constrained('bizboost_campaigns')->onDelete('set null');
                
                $table->string('name');
                $table->string('hash')->unique(); // e.g. wa/x9f82
                $table->string('destination_type')->default('whatsapp'); // whatsapp, phone, url
                $table->string('target_url');
                $table->string('utm_source')->nullable();
                $table->string('utm_medium')->nullable();
                $table->string('utm_campaign')->nullable();
                
                $table->unsignedInteger('clicks_count')->default(0);
                $table->unsignedInteger('conversions_count')->default(0);
                $table->timestamps();
            });
        }

        // 7. Create bizboost_attributions
        if (!Schema::hasTable('bizboost_attributions')) {
            Schema::create('bizboost_attributions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('business_id')->constrained('bizboost_businesses')->onDelete('cascade');
                $table->foreignId('customer_id')->constrained('bizboost_customers')->onDelete('cascade');
                $table->foreignId('lead_id')->nullable()->constrained('bizboost_leads')->onDelete('set null');
                $table->foreignId('campaign_id')->nullable()->constrained('bizboost_campaigns')->onDelete('set null');
                $table->foreignId('trackable_link_id')->nullable()->constrained('bizboost_trackable_links')->onDelete('set null');
                
                $table->string('source_type'); // stockflow_pos, bizdocs_invoice, growmart_order, manual
                $table->string('source_reference_id'); // Order # or Invoice #
                $table->decimal('attributed_amount_zmw', 12, 2);
                $table->string('attribution_model')->default('last_touch'); // first_touch, last_touch, linear
                
                $table->timestamps();

                $table->index(['business_id', 'campaign_id']);
                $table->index(['business_id', 'customer_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bizboost_attributions');
        Schema::dropIfExists('bizboost_trackable_links');
        Schema::dropIfExists('bizboost_leads');
        Schema::dropIfExists('bizboost_pipeline_stages');
        Schema::dropIfExists('bizboost_lead_pipelines');

        if (Schema::hasTable('bizboost_campaigns')) {
            Schema::table('bizboost_campaigns', function (Blueprint $table) {
                $table->dropColumn(['spend_zmw', 'attributed_revenue_zmw', 'marketing_roi_ratio']);
            });
        }

        if (Schema::hasTable('bizboost_customers')) {
            Schema::table('bizboost_customers', function (Blueprint $table) {
                $table->dropColumn(['intent_score', 'intent_tier', 'clv_zmw', 'canonical_organization_id']);
            });
        }
    }
};
