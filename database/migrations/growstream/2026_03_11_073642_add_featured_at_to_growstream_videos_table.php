<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('growstream_videos', function (Blueprint $table) {
            $table->timestamp('featured_at')->nullable()->after('is_featured');
            $table->index('featured_at');
        });
    }

    public function down(): void
    {
        Schema::table('growstream_videos', function (Blueprint $table) {
            $table->dropIndex(['featured_at']);
            $table->dropColumn('featured_at');
        });
    }
};