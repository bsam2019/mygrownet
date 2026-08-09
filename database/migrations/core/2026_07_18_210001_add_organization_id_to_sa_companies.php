<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sa_companies')) {
            if (!Schema::hasColumn('sa_companies', 'organization_id')) {
                Schema::table('sa_companies', function (Blueprint $table) {
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
        if (Schema::hasTable('sa_companies')) {
            if (Schema::hasColumn('sa_companies', 'organization_id')) {
                Schema::table('sa_companies', function (Blueprint $table) {
                    $table->dropForeign(['organization_id']);
                    $table->dropColumn('organization_id');
                });
            }
        }
    }
};
