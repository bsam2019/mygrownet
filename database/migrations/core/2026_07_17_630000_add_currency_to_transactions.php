<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sa_sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sa_sales', 'currency')) {
                $table->string('currency', 3)->default('USD')->after('total');
            }
            if (!Schema::hasColumn('sa_sales', 'exchange_rate')) {
                $table->decimal('exchange_rate', 12, 6)->nullable()->after('currency');
            }
        });

        Schema::table('sa_purchase_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('sa_purchase_orders', 'currency')) {
                $table->string('currency', 3)->default('USD')->after('total');
            }
            if (!Schema::hasColumn('sa_purchase_orders', 'exchange_rate')) {
                $table->decimal('exchange_rate', 12, 6)->nullable()->after('currency');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sa_sales', function (Blueprint $table) {
            $table->dropColumn(['currency', 'exchange_rate']);
        });

        Schema::table('sa_purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['currency', 'exchange_rate']);
        });
    }
};
