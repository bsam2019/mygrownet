<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add lgr_package_id to starter_kit_purchases (only if it doesn't exist)
        Schema::table('starter_kit_purchases', function (Blueprint $table) {
            if (!Schema::hasColumn('starter_kit_purchases', 'lgr_package_id')) {
                $table->unsignedBigInteger('lgr_package_id')->nullable()->after('tier');
                $table->foreign('lgr_package_id')->references('id')->on('lgr_packages')->onDelete('set null');
                $table->index('lgr_package_id');
            }
        });

        // Migrate existing data: map tier to lgr_package_id
        // Basic (K500) -> Package 1, Premium (K1000) -> Package 2
        if (DB::connection()->getDriverName() === 'sqlite') {
            // SQLite doesn't support UPDATE with JOIN, use subquery instead
            DB::statement("
                UPDATE starter_kit_purchases
                SET lgr_package_id = (
                    SELECT id FROM lgr_packages 
                    WHERE (starter_kit_purchases.tier = 'basic' AND lgr_packages.package_amount = 500)
                       OR (starter_kit_purchases.tier = 'premium' AND lgr_packages.package_amount = 1000)
                    LIMIT 1
                )
                WHERE EXISTS (
                    SELECT 1 FROM lgr_packages 
                    WHERE (starter_kit_purchases.tier = 'basic' AND lgr_packages.package_amount = 500)
                       OR (starter_kit_purchases.tier = 'premium' AND lgr_packages.package_amount = 1000)
                )
            ");
        } else {
            // MySQL supports UPDATE with JOIN
            DB::statement("
                UPDATE starter_kit_purchases skp
                LEFT JOIN lgr_packages lp ON (
                    (skp.tier = 'basic' AND lp.package_amount = 500) OR
                    (skp.tier = 'premium' AND lp.package_amount = 1000)
                )
                SET skp.lgr_package_id = lp.id
                WHERE lp.id IS NOT NULL
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('starter_kit_purchases', function (Blueprint $table) {
            $table->dropForeign(['lgr_package_id']);
            $table->dropIndex(['lgr_package_id']);
            $table->dropColumn('lgr_package_id');
        });
    }
};
