<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('storage_plans', function (Blueprint $table) {
            $table->decimal('price_monthly', 10, 2)->default(0)->after('is_active');
            $table->decimal('price_annual', 10, 2)->default(0)->after('price_monthly');
        });
    }

    public function down(): void
    {
        Schema::table('storage_plans', function (Blueprint $table) {
            $table->dropColumn(['price_monthly', 'price_annual']);
        });
    }
};
