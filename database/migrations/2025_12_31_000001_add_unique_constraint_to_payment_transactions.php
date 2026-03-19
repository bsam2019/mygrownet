<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Check if index already exists
        if (DB::connection()->getDriverName() === 'sqlite') {
            $indexExists = DB::select("SELECT name FROM sqlite_master WHERE type='index' AND name = 'gb_txn_ref_unique'");
            if (!empty($indexExists)) {
                return; // Index already exists, skip
            }
        }

        try {
            Schema::table('growbuilder_site_payment_transactions', function (Blueprint $table) {
                // Add the unique constraint with a shorter name
                $table->unique('transaction_reference', 'gb_txn_ref_unique');
            });
        } catch (\Exception $e) {
            // Constraint already exists, skip
            if (!str_contains($e->getMessage(), 'Duplicate key name') && !str_contains($e->getMessage(), 'already exists')) {
                throw $e;
            }
        }
    }

    public function down(): void
    {
        try {
            Schema::table('growbuilder_site_payment_transactions', function (Blueprint $table) {
                $table->dropUnique('gb_txn_ref_unique');
            });
        } catch (\Exception $e) {
            // Constraint doesn't exist, skip
            if (!str_contains($e->getMessage(), "Can't DROP")) {
                throw $e;
            }
        }
    }
};
