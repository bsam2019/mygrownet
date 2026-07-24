<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('cms_kpis')) {
            Schema::create('cms_kpis', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
                $table->string('name');
                $table->text('description')->nullable();
                $table->string('category')->default('financial');
                $table->string('unit')->nullable();
                $table->string('frequency')->default('monthly');
                $table->decimal('target_min', 15, 2)->nullable();
                $table->decimal('target_max', 15, 2)->nullable();
                $table->string('direction')->default('up');
                $table->string('owner')->nullable();
                $table->string('status')->default('active');
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        } else {
            $cols = Schema::getColumnListing('cms_kpis');
            Schema::table('cms_kpis', function (Blueprint $table) use ($cols) {
                if (!in_array('category', $cols)) $table->string('category')->default('financial')->after('description');
                if (!in_array('target_min', $cols)) $table->decimal('target_min', 15, 2)->nullable()->after('frequency');
                if (!in_array('target_max', $cols)) $table->decimal('target_max', 15, 2)->nullable()->after('target_min');
                if (!in_array('direction', $cols)) $table->string('direction')->default('up')->after('target_max');
                if (!in_array('owner', $cols)) $table->string('owner')->nullable()->after('direction');
                if (!in_array('status', $cols)) $table->string('status')->default('active')->after('owner');
                if (!in_array('sort_order', $cols)) $table->unsignedSmallInteger('sort_order')->default(0)->after('status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('cms_kpis') && !Schema::hasColumn('cms_kpis', 'category')) {
            // Only drop if we created it (detect by our columns)
            // Safer: no-op — we don't know who created the table
        }
    }
};
