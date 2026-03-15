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
        Schema::table('growbuilder_media', function (Blueprint $table) {
            $table->unsignedBigInteger('source_media_id')->nullable()->after('site_id');
            $table->foreign('source_media_id')->references('id')->on('growbuilder_media')->onDelete('cascade');
            $table->index('source_media_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('growbuilder_media', function (Blueprint $table) {
            $table->dropForeign(['source_media_id']);
            $table->dropIndex(['source_media_id']);
            $table->dropColumn('source_media_id');
        });
    }
};
