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
        if (Schema::hasTable('packages')) {
            Schema::table('packages', function (Blueprint $table) {
                if (!Schema::hasColumn('packages', 'currency')) {
                    $table->string('currency', 3)->default('ZMW')->after('price');
                }
            });
        }

        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (!Schema::hasColumn('products', 'currency')) {
                    $table->string('currency', 3)->default('ZMW')->after('price');
                }
            });
        }

        if (Schema::hasTable('marketplace_products')) {
            Schema::table('marketplace_products', function (Blueprint $table) {
                if (!Schema::hasColumn('marketplace_products', 'currency')) {
                    $table->string('currency', 3)->default('ZMW')->after('price');
                }
            });
        }

        if (Schema::hasTable('lgr_packages')) {
            Schema::table('lgr_packages', function (Blueprint $table) {
                if (!Schema::hasColumn('lgr_packages', 'currency')) {
                    $table->string('currency', 3)->default('ZMW')->after('package_amount');
                }
            });
        }

        if (Schema::hasTable('starter_kit_tier_configs')) {
            Schema::table('starter_kit_tier_configs', function (Blueprint $table) {
                if (!Schema::hasColumn('starter_kit_tier_configs', 'currency')) {
                    $table->string('currency', 3)->default('ZMW')->after('price');
                }
            });
        }

        if (Schema::hasTable('workshops')) {
            Schema::table('workshops', function (Blueprint $table) {
                if (!Schema::hasColumn('workshops', 'currency')) {
                    $table->string('currency', 3)->default('ZMW')->after('price');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('packages')) {
            Schema::table('packages', function (Blueprint $table) {
                if (Schema::hasColumn('packages', 'currency')) {
                    $table->dropColumn('currency');
                }
            });
        }

        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (Schema::hasColumn('products', 'currency')) {
                    $table->dropColumn('currency');
                }
            });
        }

        if (Schema::hasTable('marketplace_products')) {
            Schema::table('marketplace_products', function (Blueprint $table) {
                if (Schema::hasColumn('marketplace_products', 'currency')) {
                    $table->dropColumn('currency');
                }
            });
        }

        if (Schema::hasTable('lgr_packages')) {
            Schema::table('lgr_packages', function (Blueprint $table) {
                if (Schema::hasColumn('lgr_packages', 'currency')) {
                    $table->dropColumn('currency');
                }
            });
        }

        if (Schema::hasTable('starter_kit_tier_configs')) {
            Schema::table('starter_kit_tier_configs', function (Blueprint $table) {
                if (Schema::hasColumn('starter_kit_tier_configs', 'currency')) {
                    $table->dropColumn('currency');
                }
            });
        }

        if (Schema::hasTable('workshops')) {
            Schema::table('workshops', function (Blueprint $table) {
                if (Schema::hasColumn('workshops', 'currency')) {
                    $table->dropColumn('currency');
                }
            });
        }
    }
};
