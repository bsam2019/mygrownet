<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('cms_companies')) {
            if (!Schema::hasColumn('cms_companies', 'organization_id')) {
                Schema::table('cms_companies', function (Blueprint $table) {
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
        if (Schema::hasTable('cms_companies')) {
            if (Schema::hasColumn('cms_companies', 'organization_id')) {
                Schema::table('cms_companies', function (Blueprint $table) {
                    $table->dropForeign(['organization_id']);
                    $table->dropColumn('organization_id');
                });
            }
        }
    }
};
