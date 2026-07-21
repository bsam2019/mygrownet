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

        // Drop sa_users FK constraint from sa_company_users, then the table
        if (Schema::hasTable('sa_users')) {
            Schema::table('sa_company_users', function (Blueprint $table) {
                if (Schema::hasColumn('sa_company_users', 'user_id')) {
                    $table->dropForeign(['user_id']);
                }
            });

            Schema::dropIfExists('sa_users');
        }
    }

    public function down(): void
    {
        // Restore is not implemented — legacy tables cannot be recreated with all FK deps
    }
};
