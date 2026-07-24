<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wedding_templates', function (Blueprint $table) {
            if (!Schema::hasColumn('wedding_templates', 'category')) {
                $table->string('category', 50)->default('wedding')->after('slug');
            }
            if (!Schema::hasColumn('wedding_templates', 'category_name')) {
                $table->string('category_name', 50)->default('Wedding')->after('category');
            }
            if (!Schema::hasColumn('wedding_templates', 'category_icon')) {
                $table->string('category_icon', 10)->default('💍')->after('category_name');
            }
            if (!Schema::hasColumn('wedding_templates', 'preview_text')) {
                $table->string('preview_text', 100)->nullable()->after('category_icon');
            }
        });
    }

    public function down(): void
    {
        Schema::table('wedding_templates', function (Blueprint $table) {
            $columns = ['category', 'category_name', 'category_icon', 'preview_text'];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('wedding_templates', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
