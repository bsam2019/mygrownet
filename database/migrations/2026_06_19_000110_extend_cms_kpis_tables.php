<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $kpiCols = Schema::getColumnListing('cms_kpis');

        $additions = [
            'category' => ['type' => 'string', 'default' => 'financial'],
            'target_min' => ['type' => 'decimal'],
            'target_max' => ['type' => 'decimal'],
            'direction' => ['type' => 'string', 'default' => 'up'],
            'owner' => ['type' => 'string'],
            'sort_order' => ['type' => 'unsignedSmallInteger', 'default' => 0],
        ];

        $needsAlter = false;
        foreach ($additions as $col => $cfg) {
            if (!in_array($col, $kpiCols)) {
                $needsAlter = true;
                break;
            }
        }

        if ($needsAlter) {
            Schema::table('cms_kpis', function (Blueprint $table) use ($kpiCols) {
                if (!in_array('category', $kpiCols)) {
                    $table->string('category')->default('financial')->after('description');
                }
                if (!in_array('target_min', $kpiCols)) {
                    $table->decimal('target_min', 15, 2)->nullable()->after('frequency');
                }
                if (!in_array('target_max', $kpiCols)) {
                    $table->decimal('target_max', 15, 2)->nullable()->after('target_min');
                }
                if (!in_array('direction', $kpiCols)) {
                    $table->string('direction')->default('up')->after('target_max');
                }
                if (!in_array('owner', $kpiCols)) {
                    $table->string('owner')->nullable()->after('calculation_method');
                }
                if (!in_array('sort_order', $kpiCols)) {
                    $table->unsignedSmallInteger('sort_order')->default(0)->after('is_active');
                }
            });
        }

        $valueCols = Schema::getColumnListing('cms_kpi_values');

        $valueAdditions = [
            'period_start' => ['type' => 'date'],
            'period_end' => ['type' => 'date'],
            'notes' => ['type' => 'text'],
            'recorded_by' => ['type' => 'foreignId'],
        ];

        $needsValueAlter = false;
        foreach ($valueAdditions as $col => $cfg) {
            if (!in_array($col, $valueCols)) {
                $needsValueAlter = true;
                break;
            }
        }

        if ($needsValueAlter) {
            Schema::table('cms_kpi_values', function (Blueprint $table) use ($valueCols) {
                if (!in_array('period_start', $valueCols)) {
                    $table->date('period_start')->nullable()->after('kpi_id');
                }
                if (!in_array('period_end', $valueCols)) {
                    $table->date('period_end')->nullable()->after('period_start');
                }
                if (!in_array('notes', $valueCols)) {
                    $table->text('notes')->nullable()->after('variance_percentage');
                }
                if (!in_array('recorded_by', $valueCols)) {
                    $table->foreignId('recorded_by')->nullable()->after('notes')
                        ->constrained('users')->nullOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        // No down — this is additive only
    }
};
