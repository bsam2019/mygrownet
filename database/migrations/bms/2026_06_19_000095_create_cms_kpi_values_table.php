<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('cms_kpi_values')) {
            Schema::create('cms_kpi_values', function (Blueprint $table) {
                $table->id();
                $table->foreignId('kpi_id')->constrained('cms_kpis')->cascadeOnDelete();
                $table->date('period_start');
                $table->date('period_end');
                $table->decimal('value', 15, 2);
                $table->text('notes')->nullable();
                $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['kpi_id', 'period_start']);
            });
        } else {
            $cols = Schema::getColumnListing('cms_kpi_values');
            Schema::table('cms_kpi_values', function (Blueprint $table) use ($cols) {
                if (!in_array('period_start', $cols)) $table->date('period_start')->nullable()->after('kpi_id');
                if (!in_array('period_end', $cols)) $table->date('period_end')->nullable()->after('period_start');
                if (!in_array('notes', $cols)) $table->text('notes')->nullable()->after('value');
                if (!in_array('recorded_by', $cols)) {
                    $table->foreignId('recorded_by')->nullable()->after('notes')
                        ->constrained('users')->nullOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        // no-op for safety
    }
};
