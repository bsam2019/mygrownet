<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('bizboost_businesses')) {
            if (!Schema::hasColumn('bizboost_businesses', 'organization_id')) {
                Schema::table('bizboost_businesses', function (Blueprint $table) {
                    $table->foreignId('organization_id')
                        ->nullable()
                        ->after('id')
                        ->constrained('organizations')
                        ->nullOnDelete();
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('bizboost_businesses')) {
            if (Schema::hasColumn('bizboost_businesses', 'organization_id')) {
                Schema::table('bizboost_businesses', function (Blueprint $table) {
                    $table->dropForeign(['organization_id']);
                    $table->dropColumn('organization_id');
                });
            }
        }
    }
};
