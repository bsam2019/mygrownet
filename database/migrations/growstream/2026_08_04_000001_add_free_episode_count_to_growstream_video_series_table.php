<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('growstream_video_series', function (Blueprint $table) {
            if (! Schema::hasColumn('growstream_video_series', 'free_episode_count')) {
                $table->integer('free_episode_count')->default(1)->after('total_episodes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('growstream_video_series', function (Blueprint $table) {
            if (Schema::hasColumn('growstream_video_series', 'free_episode_count')) {
                $table->dropColumn('free_episode_count');
            }
        });
    }
};
