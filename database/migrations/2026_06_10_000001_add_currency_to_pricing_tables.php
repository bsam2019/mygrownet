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
        Schema::table('packages', function (Blueprint $table) {
            if (!Schema::hasColumn('packages', 'currency')) {
                $table->string('currency', 3)->default('ZMW')->after('price');
            }
        });

        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'currency')) {
                $table->string('currency', 3)->default('ZMW')->after('price');
            }
        });

        Schema::table('marketplace_products', function (Blueprint $table) {
            if (!Schema::hasColumn('marketplace_products', 'currency')) {
                $table->string('currency', 3)->default('ZMW')->after('price');
            }
        });

        Schema::table('lgr_packages', function (Blueprint $table) {
            if (!Schema::hasColumn('lgr_packages', 'currency')) {
                $table->string('currency', 3)->default('ZMW')->after('package_amount');
            }
        });

        Schema::table('starter_kit_tier_configs', function (Blueprint $table) {
            if (!Schema::hasColumn('starter_kit_tier_configs', 'currency')) {
                $table->string('currency', 3)->default('ZMW')->after('price');
            }
        });

        Schema::table('workshops', function (Blueprint $table) {
            if (!Schema::hasColumn('workshops', 'currency')) {
                $table->string('currency', 3)->default('ZMW')->after('price');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn('currency');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('currency');
        });

        Schema::table('marketplace_products', function (Blueprint $table) {
            $table->dropColumn('currency');
        });

        Schema::table('lgr_packages', function (Blueprint $table) {
            $table->dropColumn('currency');
        });

        Schema::table('starter_kit_tier_configs', function (Blueprint $table) {
            $table->dropColumn('currency');
        });

        Schema::table('workshops', function (Blueprint $table) {
            $table->dropColumn('currency');
        });
    }
};
