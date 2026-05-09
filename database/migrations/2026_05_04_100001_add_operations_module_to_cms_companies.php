<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('cms_companies', 'has_operations_module')) {
            Schema::table('cms_companies', function (Blueprint $table) {
                $table->boolean('has_operations_module')->default(false);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('cms_companies', 'has_operations_module')) {
            Schema::table('cms_companies', function (Blueprint $table) {
                $table->dropColumn('has_operations_module');
            });
        }
    }
};
