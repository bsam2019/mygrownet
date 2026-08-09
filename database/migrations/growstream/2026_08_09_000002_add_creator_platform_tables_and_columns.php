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
        // 1. Add organization_id and publishing_destination to videos & series
        if (Schema::hasTable('growstream_videos')) {
            Schema::table('growstream_videos', function (Blueprint $table) {
                if (!Schema::hasColumn('growstream_videos', 'organization_id')) {
                    $table->unsignedBigInteger('organization_id')->nullable()->after('creator_id');
                }
                if (!Schema::hasColumn('growstream_videos', 'publishing_destination')) {
                    $table->string('publishing_destination', 20)->default('public')->after('access_level');
                }
            });
        }

        if (Schema::hasTable('growstream_video_series')) {
            Schema::table('growstream_video_series', function (Blueprint $table) {
                if (!Schema::hasColumn('growstream_video_series', 'organization_id')) {
                    $table->unsignedBigInteger('organization_id')->nullable()->after('creator_id');
                }
                if (!Schema::hasColumn('growstream_video_series', 'publishing_destination')) {
                    $table->string('publishing_destination', 20)->default('public')->after('access_level');
                }
            });
        }

        // 2. Creator Platforms Table (Client Platforms managed by GrowStream Hub)
        if (!Schema::hasTable('growstream_creator_platforms')) {
            Schema::create('growstream_creator_platforms', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('organization_id')->unique();
                $table->string('subdomain', 100)->nullable()->unique();
                $table->string('custom_domain', 255)->nullable()->unique();
                $table->string('brand_name', 255)->nullable();
                $table->string('brand_color', 30)->default('#e2571f');
                $table->string('logo_url', 2048)->nullable();
                $table->string('banner_url', 2048)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 3. Platform Quotas Table (Storage & Bandwidth Metering)
        if (!Schema::hasTable('growstream_platform_quotas')) {
            Schema::create('growstream_platform_quotas', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('organization_id')->unique();
                $table->integer('storage_minutes_limit')->default(1000);
                $table->integer('delivery_gb_limit')->default(100);
                $table->integer('current_storage_minutes')->default(0);
                $table->integer('current_delivery_gb')->default(0);
                $table->timestamps();
            });
        }

        // 4. Platform Gateways Table (Bring Your Own Payment - BYOP)
        if (!Schema::hasTable('growstream_platform_gateways')) {
            Schema::create('growstream_platform_gateways', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('organization_id');
                $table->string('gateway_slug', 50);
                $table->text('credentials_encrypted');
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['organization_id', 'gateway_slug']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('growstream_platform_gateways');
        Schema::dropIfExists('growstream_platform_quotas');
        Schema::dropIfExists('growstream_creator_platforms');

        if (Schema::hasTable('growstream_video_series')) {
            Schema::table('growstream_video_series', function (Blueprint $table) {
                if (Schema::hasColumn('growstream_video_series', 'publishing_destination')) {
                    $table->dropColumn('publishing_destination');
                }
                if (Schema::hasColumn('growstream_video_series', 'organization_id')) {
                    $table->dropColumn('organization_id');
                }
            });
        }

        if (Schema::hasTable('growstream_videos')) {
            Schema::table('growstream_videos', function (Blueprint $table) {
                if (Schema::hasColumn('growstream_videos', 'publishing_destination')) {
                    $table->dropColumn('publishing_destination');
                }
                if (Schema::hasColumn('growstream_videos', 'organization_id')) {
                    $table->dropColumn('organization_id');
                }
            });
        }
    }
};
