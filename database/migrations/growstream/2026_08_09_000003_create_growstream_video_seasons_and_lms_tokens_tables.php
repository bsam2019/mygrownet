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
        // 1. GrowStream Video Seasons Table
        if (!Schema::hasTable('growstream_video_seasons')) {
            Schema::create('growstream_video_seasons', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('series_id');
                $table->integer('season_number')->default(1);
                $table->string('title', 255);
                $table->text('description')->nullable();
                $table->integer('release_year')->nullable();
                $table->string('poster_url', 2048)->nullable();
                $table->timestamps();

                $table->unique(['series_id', 'season_number']);
            });
        }

        // 2. Add season_id to growstream_videos
        if (Schema::hasTable('growstream_videos')) {
            Schema::table('growstream_videos', function (Blueprint $table) {
                if (!Schema::hasColumn('growstream_videos', 'season_id')) {
                    $table->unsignedBigInteger('season_id')->nullable()->after('series_id');
                }
            });
        }

        // 3. Moodle LMS Tokens Table
        if (!Schema::hasTable('growstream_lms_tokens')) {
            Schema::create('growstream_lms_tokens', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('organization_id');
                $table->string('api_token', 80)->unique();
                $table->string('moodle_url', 2048)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('growstream_lms_tokens');

        if (Schema::hasTable('growstream_videos')) {
            Schema::table('growstream_videos', function (Blueprint $table) {
                if (Schema::hasColumn('growstream_videos', 'season_id')) {
                    $table->dropColumn('season_id');
                }
            });
        }

        Schema::dropIfExists('growstream_video_seasons');
    }
};
