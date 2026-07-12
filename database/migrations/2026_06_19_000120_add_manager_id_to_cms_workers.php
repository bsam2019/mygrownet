<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('cms_workers', 'manager_id')) {
            Schema::table('cms_workers', function (Blueprint $table) {
                $table->foreignId('manager_id')->nullable()->after('department_id')
                    ->constrained('cms_workers')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('cms_workers', function (Blueprint $table) {
            $table->dropForeign(['manager_id']);
            $table->dropColumn('manager_id');
        });
    }
};
