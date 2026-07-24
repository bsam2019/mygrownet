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
        Schema::table('growbuilder_sites', function (Blueprint $table) {
            $table->unsignedBigInteger('client_id')->nullable()->after('user_id');
            $table->foreign('client_id')->references('id')->on('agency_clients')->onDelete('set null');
            $table->index(['user_id', 'client_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('growbuilder_sites', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropIndex(['user_id', 'client_id']);
            $table->dropColumn('client_id');
        });
    }
};
