<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('cms_invoices') && Schema::hasColumn('cms_invoices', 'status')) {
            Schema::table('cms_invoices', function (Blueprint $table) {
                if (!Schema::hasColumn('cms_invoices', 'source')) {
                    $table->string('source')->default('manual')->after('status');
                }
                if (!Schema::hasColumn('cms_invoices', 'metadata')) {
                    $table->json('metadata')->nullable()->after('notes');
                }
            });
        }

        if (Schema::hasTable('cms_customers') && Schema::hasColumn('cms_customers', 'type')) {
            Schema::table('cms_customers', function (Blueprint $table) {
                if (!Schema::hasColumn('cms_customers', 'source')) {
                    $table->string('source')->default('manual')->after('type');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('cms_invoices')) {
            Schema::table('cms_invoices', function (Blueprint $table) {
                if (Schema::hasColumn('cms_invoices', 'source')) {
                    $table->dropColumn(['source', 'metadata']);
                }
            });
        }

        if (Schema::hasTable('cms_customers')) {
            Schema::table('cms_customers', function (Blueprint $table) {
                if (Schema::hasColumn('cms_customers', 'source')) {
                    $table->dropColumn('source');
                }
            });
        }
    }
};
