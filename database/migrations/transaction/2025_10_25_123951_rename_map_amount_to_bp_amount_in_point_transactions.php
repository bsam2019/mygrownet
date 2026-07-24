<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('point_transactions', function (Blueprint $table) {
            $table->renameColumn('map_amount', 'bp_amount');
        });
    }

    public function down(): void
    {
        Schema::table('point_transactions', function (Blueprint $table) {
            $table->renameColumn('bp_amount', 'map_amount');
        });
    }
};
