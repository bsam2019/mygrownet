<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Extend growbuilder_sites with Business Profile, SSG, and Template Versioning columns ──
        if (Schema::hasTable('growbuilder_sites')) {
            Schema::table('growbuilder_sites', function (Blueprint $table) {
                if (!Schema::hasColumn('growbuilder_sites', 'canonical_organization_id')) {
                    $table->unsignedBigInteger('canonical_organization_id')->nullable()->after('user_id');
                }
                if (!Schema::hasColumn('growbuilder_sites', 'pwa_enabled')) {
                    $table->boolean('pwa_enabled')->default(false)->after('status');
                }
                if (!Schema::hasColumn('growbuilder_sites', 'ssg_enabled')) {
                    $table->boolean('ssg_enabled')->default(false)->after('pwa_enabled');
                }
                if (!Schema::hasColumn('growbuilder_sites', 'theme_preset')) {
                    $table->string('theme_preset', 50)->nullable()->after('ssg_enabled');
                }
                if (!Schema::hasColumn('growbuilder_sites', 'last_ssg_deployed_at')) {
                    $table->timestamp('last_ssg_deployed_at')->nullable()->after('theme_preset');
                }
                if (!Schema::hasColumn('growbuilder_sites', 'template_version')) {
                    $table->unsignedInteger('template_version')->default(1)->after('template_id');
                }
                if (!Schema::hasColumn('growbuilder_sites', 'template_locked')) {
                    $table->boolean('template_locked')->default(false)->after('template_version');
                }
                if (!Schema::hasColumn('growbuilder_sites', 'last_template_sync')) {
                    $table->timestamp('last_template_sync')->nullable()->after('template_locked');
                }
            });
        }

        // ── 2. Extend site_templates with versioning support ──
        if (Schema::hasTable('site_templates')) {
            Schema::table('site_templates', function (Blueprint $table) {
                if (!Schema::hasColumn('site_templates', 'current_version')) {
                    $table->unsignedInteger('current_version')->default(1)->after('slug');
                }
                if (!Schema::hasColumn('site_templates', 'version_history')) {
                    $table->json('version_history')->nullable()->after('current_version');
                }
                if (!Schema::hasColumn('site_templates', 'upgrade_strategy')) {
                    $table->enum('upgrade_strategy', ['merge', 'replace', 'manual'])->default('merge')->after('version_history');
                }
            });
        }

        // ── 3. Create growbuilder_business_profiles ──
        if (!Schema::hasTable('growbuilder_business_profiles')) {
            Schema::create('growbuilder_business_profiles', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('site_id')->index();
                $table->unsignedBigInteger('organization_id')->nullable()->index();
                $table->string('legal_name')->nullable();
                $table->string('trade_name')->nullable();
                $table->string('tpin', 50)->nullable();
                $table->string('pacra_number', 100)->nullable();
                $table->string('phone', 30)->nullable();
                $table->string('whatsapp', 30)->nullable();
                $table->string('email', 191)->nullable();
                $table->string('website', 191)->nullable();
                $table->text('physical_address')->nullable();
                $table->string('city', 100)->nullable();
                $table->string('province', 100)->nullable();
                $table->string('country', 10)->default('ZM');
                $table->string('industry', 100)->nullable();
                $table->string('industry_blueprint', 100)->nullable(); // 'pharmacy', 'restaurant', 'school'
                $table->json('opening_hours')->nullable();  // structured MON-SUN hours
                $table->json('services_json')->nullable();  // [{name, description, price}]
                $table->json('trust_badges_json')->nullable(); // PACRA verified, TPIN, etc.
                $table->json('payment_methods')->nullable(); // ['mtn_momo', 'airtel_money', 'cash']
                $table->string('logo_path', 500)->nullable();
                $table->string('banner_path', 500)->nullable();
                $table->text('tagline')->nullable();
                $table->text('description')->nullable();
                $table->string('price_range', 20)->nullable(); // '$', '$$', '$$$'
                $table->decimal('latitude', 10, 7)->nullable();
                $table->decimal('longitude', 10, 7)->nullable();
                $table->string('google_place_id', 200)->nullable();
                $table->boolean('pacra_verified')->default(false);
                $table->boolean('tpin_verified')->default(false);
                $table->timestamps();
                $table->foreign('site_id')->references('id')->on('growbuilder_sites')->onDelete('cascade');
            });
        }

        // ── 4. Create growbuilder_page_revisions ──
        if (!Schema::hasTable('growbuilder_page_revisions')) {
            Schema::create('growbuilder_page_revisions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('site_id')->index();
                $table->unsignedBigInteger('page_id')->index();
                $table->unsignedInteger('revision_number')->default(1);
                $table->json('layout_json');
                $table->unsignedBigInteger('created_by_user_id')->nullable();
                $table->string('commit_message', 255)->nullable();
                $table->string('trigger', 50)->default('manual'); // 'manual', 'auto_save', 'pre_upgrade'
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();
                $table->foreign('site_id')->references('id')->on('growbuilder_sites')->onDelete('cascade');
                $table->index(['page_id', 'revision_number']);
            });
        }

        // ── 5. Create growbuilder_ssg_deployments ──
        if (!Schema::hasTable('growbuilder_ssg_deployments')) {
            Schema::create('growbuilder_ssg_deployments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('site_id')->index();
                $table->enum('status', ['pending', 'building', 'deployed', 'failed'])->default('pending');
                $table->string('asset_zip_path', 500)->nullable();
                $table->string('cdn_url', 500)->nullable();
                $table->text('build_log')->nullable();
                $table->unsignedInteger('build_duration_ms')->nullable();
                $table->timestamp('deployed_at')->nullable();
                $table->string('triggered_by', 50)->default('publish'); // 'publish', 'update', 'manual'
                $table->timestamps();
                $table->foreign('site_id')->references('id')->on('growbuilder_sites')->onDelete('cascade');
            });
        }

        // ── 6. Create growbuilder_site_snapshots (template upgrade rollback) ──
        if (!Schema::hasTable('growbuilder_site_snapshots')) {
            Schema::create('growbuilder_site_snapshots', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('site_id')->index();
                $table->enum('snapshot_type', ['pre_upgrade', 'manual', 'auto_backup'])->default('manual');
                $table->longText('pages_json');
                $table->json('design_tokens_json')->nullable();
                $table->json('metadata')->nullable(); // template_version, trigger, user notes
                $table->unsignedBigInteger('created_by_user_id')->nullable();
                $table->timestamp('expires_at')->nullable(); // 90-day auto-cleanup
                $table->timestamps();
                $table->foreign('site_id')->references('id')->on('growbuilder_sites')->onDelete('cascade');
            });
        }

        // ── 7. Create growbuilder_qr_codes (physical-to-digital bridge) ──
        if (!Schema::hasTable('growbuilder_qr_codes')) {
            Schema::create('growbuilder_qr_codes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('site_id')->index();
                $table->string('code', 32)->unique()->index();
                $table->string('target_url', 1000);
                $table->string('label', 191)->nullable(); // 'main', 'product-catalog', 'whatsapp'
                $table->string('utm_source', 191)->nullable();
                $table->string('utm_medium', 191)->nullable();
                $table->string('utm_campaign', 191)->nullable();
                $table->string('image_path', 500)->nullable();
                $table->unsignedBigInteger('scan_count')->default(0);
                $table->timestamps();
                $table->foreign('site_id')->references('id')->on('growbuilder_sites')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('growbuilder_qr_codes');
        Schema::dropIfExists('growbuilder_site_snapshots');
        Schema::dropIfExists('growbuilder_ssg_deployments');
        Schema::dropIfExists('growbuilder_page_revisions');
        Schema::dropIfExists('growbuilder_business_profiles');

        if (Schema::hasTable('site_templates')) {
            Schema::table('site_templates', function (Blueprint $table) {
                $table->dropColumnIfExists(['current_version', 'version_history', 'upgrade_strategy']);
            });
        }

        if (Schema::hasTable('growbuilder_sites')) {
            Schema::table('growbuilder_sites', function (Blueprint $table) {
                $table->dropColumnIfExists([
                    'canonical_organization_id', 'pwa_enabled', 'ssg_enabled', 'theme_preset',
                    'last_ssg_deployed_at', 'template_version', 'template_locked', 'last_template_sync',
                ]);
            });
        }
    }
};
