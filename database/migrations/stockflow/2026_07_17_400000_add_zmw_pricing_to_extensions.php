<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('extensions', function (Blueprint $table) {
            if (Schema::hasColumn('extensions', 'price_monthly') && !Schema::hasColumn('extensions', 'price_monthly_usd')) {
                $table->renameColumn('price_monthly', 'price_monthly_usd');
            }
            if (Schema::hasColumn('extensions', 'price_yearly') && !Schema::hasColumn('extensions', 'price_yearly_usd')) {
                $table->renameColumn('price_yearly', 'price_yearly_usd');
            }
            if (!Schema::hasColumn('extensions', 'price_monthly_zmw')) {
                $table->decimal('price_monthly_zmw', 10, 2)->default(0)->after('price_yearly_usd');
            }
            if (!Schema::hasColumn('extensions', 'price_yearly_zmw')) {
                $table->decimal('price_yearly_zmw', 10, 2)->default(0)->after('price_monthly_zmw');
            }
        });
    }

    public function down(): void
    {
        Schema::table('extensions', function (Blueprint $table) {
            $table->dropColumn(['price_monthly_zmw', 'price_yearly_zmw']);
            if (Schema::hasColumn('extensions', 'price_monthly_usd')) {
                $table->renameColumn('price_monthly_usd', 'price_monthly');
            }
            if (Schema::hasColumn('extensions', 'price_yearly_usd')) {
                $table->renameColumn('price_yearly_usd', 'price_yearly');
            }
        });
    }
};
