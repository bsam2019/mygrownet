<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cms_quotation_items', function (Blueprint $table) {
            // Dimensions string e.g. "1200mm × 900mm" — shown on PDF
            $table->string('dimensions', 100)->nullable()->after('line_total');
            // Effective multiplier (total_area for fabrication items, 1 for standard items)
            $table->decimal('dimensions_value', 10, 4)->default(1)->after('dimensions');
        });
    }

    public function down(): void
    {
        Schema::table('cms_quotation_items', function (Blueprint $table) {
            $table->dropColumn(['dimensions', 'dimensions_value']);
        });
    }
};
