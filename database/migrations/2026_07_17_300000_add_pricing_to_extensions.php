<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('extensions', function (Blueprint $table) {
            $table->decimal('price_monthly', 10, 2)->default(0)->after('is_active');
            $table->decimal('price_yearly', 10, 2)->default(0)->after('price_monthly');
        });
    }

    public function down(): void
    {
        Schema::table('extensions', function (Blueprint $table) {
            $table->dropColumn(['price_monthly', 'price_yearly']);
        });
    }
};
