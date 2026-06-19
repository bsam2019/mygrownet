<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('plan_objectives', 'kpi_id')) {
            Schema::table('plan_objectives', function (Blueprint $table) {
                $table->foreignId('kpi_id')->nullable()->after('plan_id')
                    ->constrained('cms_kpis')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('plan_objectives', function (Blueprint $table) {
            $table->dropForeign(['kpi_id']);
            $table->dropColumn('kpi_id');
        });
    }
};
