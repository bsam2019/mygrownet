<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('module_tiers', function (Blueprint $table) {
            $table->boolean('is_popular')->default(false)->after('is_default');
        });
    }

    public function down(): void
    {
        Schema::table('module_tiers', function (Blueprint $table) {
            $table->dropColumn('is_popular');
        });
    }
};
