<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('quick_invoice_profiles')) {
            if (!Schema::hasColumn('quick_invoice_profiles', 'organization_id')) {
                Schema::table('quick_invoice_profiles', function (Blueprint $table) {
                    $table->foreignId('organization_id')
                        ->nullable()
                        ->after('user_id')
                        ->constrained('organizations')
                        ->nullOnDelete();
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('quick_invoice_profiles')) {
            if (Schema::hasColumn('quick_invoice_profiles', 'organization_id')) {
                Schema::table('quick_invoice_profiles', function (Blueprint $table) {
                    $table->dropForeign(['organization_id']);
                    $table->dropColumn('organization_id');
                });
            }
        }
    }
};
