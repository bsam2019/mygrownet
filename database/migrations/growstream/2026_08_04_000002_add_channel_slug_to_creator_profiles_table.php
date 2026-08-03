<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('growstream_creator_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('growstream_creator_profiles', 'channel_slug')) {
                $table->string('channel_slug', 120)->nullable()->unique()->after('display_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('growstream_creator_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('growstream_creator_profiles', 'channel_slug')) {
                $table->dropUnique(['channel_slug']);
                $table->dropColumn('channel_slug');
            }
        });
    }
};
