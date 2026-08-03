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

        $has = fn (string $col) => Schema::hasColumn('marketplace_payouts', $col);

        // Change status from enum to string to support 'approved','rejected' values
        if ($has('status')) {
            Schema::table('marketplace_payouts', function (Blueprint $table) {
                $table->string('status', 50)->default('pending')->change();
            });
        }

        Schema::table('marketplace_payouts', function (Blueprint $table) use ($has) {
            // Rename method -> payout_method (skip if already renamed or missing)
            if ($has('method') && !$has('payout_method')) {
                $table->renameColumn('method', 'payout_method');
            }

            // Add new financial columns (only if missing)
            if (!$has('commission_deducted')) $table->integer('commission_deducted')->default(0);
            if (!$has('net_amount')) $table->integer('net_amount')->default(0);
            if (!$has('bank_name')) $table->string('bank_name')->nullable();

            // Rename reference_number -> reference (skip if already renamed or missing)
            if ($has('reference_number') && !$has('reference')) {
                $table->renameColumn('reference_number', 'reference');
            }

            // Add notes columns
            if (!$has('seller_notes')) $table->text('seller_notes')->nullable();
            if (!$has('admin_notes')) $table->text('admin_notes')->nullable();
            if (!$has('rejection_reason')) $table->text('rejection_reason')->nullable();

            // Add admin action tracking columns
            if (!$has('approved_by')) $table->foreignId('approved_by')->nullable()->constrained('users');
            if (!$has('approved_at')) $table->timestamp('approved_at')->nullable();
            if (!$has('processed_by')) $table->foreignId('processed_by')->nullable()->constrained('users');
            if (!$has('transaction_reference')) $table->string('transaction_reference')->nullable();
            if (!$has('metadata')) $table->json('metadata')->nullable();

            // Drop unused columns (only if present)
            if ($has('failure_reason')) $table->dropColumn('failure_reason');
            if ($has('completed_at')) $table->dropColumn('completed_at');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('marketplace_payouts')) {
            return;
        }

        $has = fn (string $col) => Schema::hasColumn('marketplace_payouts', $col);

        Schema::table('marketplace_payouts', function (Blueprint $table) use ($has) {
            if ($has('commission_deducted')) $table->dropColumn('commission_deducted');
            if ($has('net_amount')) $table->dropColumn('net_amount');
            if ($has('bank_name')) $table->dropColumn('bank_name');
            if ($has('seller_notes')) $table->dropColumn('seller_notes');
            if ($has('admin_notes')) $table->dropColumn('admin_notes');
            if ($has('rejection_reason')) $table->dropColumn('rejection_reason');
            if ($has('approved_by')) $table->dropColumn('approved_by');
            if ($has('approved_at')) $table->dropColumn('approved_at');
            if ($has('processed_by')) $table->dropColumn('processed_by');
            if ($has('transaction_reference')) $table->dropColumn('transaction_reference');
            if ($has('metadata')) $table->dropColumn('metadata');

            if ($has('payout_method') && !$has('method')) {
                $table->renameColumn('payout_method', 'method');
            }
            if ($has('reference') && !$has('reference_number')) {
                $table->renameColumn('reference', 'reference_number');
            }
            if (!$has('failure_reason')) $table->text('failure_reason')->nullable();
            if (!$has('completed_at')) $table->timestamp('completed_at')->nullable();
        });
    }
};
