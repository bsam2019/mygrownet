<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop prime_edge_clients FK constraints first, then the table
        // No records exist — safe to drop directly
        if (Schema::hasTable('prime_edge_clients')) {
            Schema::table('prime_edge_appointments', function (Blueprint $table) {
                $table->dropForeign(['client_id']);
            });
            Schema::table('prime_edge_inquiries', function (Blueprint $table) {
                $table->dropForeign(['client_id']);
            });
            Schema::table('prime_edge_compliance_tasks', function (Blueprint $table) {
                $table->dropForeign(['client_id']);
            });
            Schema::table('prime_edge_documents', function (Blueprint $table) {
                $table->dropForeign(['client_id']);
            });
            Schema::table('prime_edge_engagements', function (Blueprint $table) {
                $table->dropForeign(['client_id']);
            });
            Schema::table('prime_edge_invoices', function (Blueprint $table) {
                $table->dropForeign(['client_id']);
            });

            Schema::dropIfExists('prime_edge_clients');
        }

        // Drop sa_users FK constraints first, then the table.
        // sa_users is referenced by 11 FKs across StockFlow tables — all must be
        // dropped before the table itself or MySQL throws 3730.
        if (Schema::hasTable('sa_users')) {
            $saUserFks = [
                ['sa_company_users', 'user_id'],
                ['sa_comments', 'sa_user_id'],
                ['sa_controlled_medicines', 'staff_user_id'],
                ['sa_messages', 'recipient_id'],
                ['sa_messages', 'sender_id'],
                ['sa_notifications', 'sa_user_id'],
                ['sa_purchase_requisitions', 'approved_by'],
                ['sa_purchase_requisitions', 'requested_by'],
                ['sa_sale_returns', 'created_by'],
                ['sa_supplier_returns', 'created_by'],
                ['sa_transfers', 'received_by'],
                ['sa_transfers', 'transferred_by'],
            ];

            foreach ($saUserFks as [$table, $column]) {
                if (Schema::hasTable($table) && Schema::hasColumn($table, $column)) {
                    try {
                        Schema::table($table, function (Blueprint $blueprint) use ($column) {
                            $blueprint->dropForeign([$column]);
                        });
                    } catch (\Throwable $e) {
                        // Foreign key may already be absent — ignore.
                    }
                }
            }

            Schema::dropIfExists('sa_users');
        }
    }

    public function down(): void
    {
        // Restore is not implemented — legacy tables cannot be recreated with all FK deps
    }
};
