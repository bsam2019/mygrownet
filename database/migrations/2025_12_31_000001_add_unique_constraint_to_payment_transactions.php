<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        try {
            Schema::table('growbuilder_site_payment_transactions', function (Blueprint $table) {
                // Add the unique constraint with a shorter name
                $table->unique('transaction_reference', 'gb_txn_ref_unique');
            });
        } catch (\Exception $e) {
            // Constraint already exists, skip
            if (!str_contains($e->getMessage(), 'Duplicate key name')) {
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
