<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('marketplace_payouts')) {
            return;
        }

        // Change status from enum to string to support 'approved','rejected' values
        Schema::table('marketplace_payouts', function (Blueprint $table) {
            $table->string('status', 50)->default('pending')->change();
        });

        Schema::table('marketplace_payouts', function (Blueprint $table) {
            // Rename method -> payout_method
            $table->renameColumn('method', 'payout_method');

            // Add new financial columns
            $table->integer('commission_deducted')->default(0);
            $table->integer('net_amount')->default(0);
            $table->string('bank_name')->nullable();

            // Rename reference_number -> reference
            $table->renameColumn('reference_number', 'reference');

            // Add notes columns
            $table->text('seller_notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->text('rejection_reason')->nullable();

            // Add admin action tracking columns
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users');
            $table->string('transaction_reference')->nullable();
            $table->json('metadata')->nullable();

            // Drop unused column
            $table->dropColumn('failure_reason');
            $table->dropColumn('completed_at');
        });
    }

    public function down(): void
    {
        Schema::table('marketplace_payouts', function (Blueprint $table) {
            $table->dropColumn([
                'commission_deducted', 'net_amount', 'bank_name',
                'seller_notes', 'admin_notes', 'rejection_reason',
                'approved_by', 'approved_at', 'processed_by',
                'transaction_reference', 'metadata',
            ]);
            $table->renameColumn('payout_method', 'method');
            $table->renameColumn('reference', 'reference_number');
            $table->text('failure_reason')->nullable();
            $table->timestamp('completed_at')->nullable();
        });
    }
};
