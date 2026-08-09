<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('growfinance_profiles')) {
            if (!Schema::hasColumn('growfinance_profiles', 'organization_id')) {
                Schema::table('growfinance_profiles', function (Blueprint $table) {
                    $table->foreignId('organization_id')
                        ->nullable()
                        ->constrained('organizations')
                        ->cascadeOnDelete()
                        ->after('id');
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('growfinance_profiles')) {
            if (Schema::hasColumn('growfinance_profiles', 'organization_id')) {
                Schema::table('growfinance_profiles', function (Blueprint $table) {
                    $table->dropForeign(['organization_id']);
                    $table->dropColumn('organization_id');
                });
            }
        }
    }
};