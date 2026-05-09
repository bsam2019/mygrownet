<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('cms_quotation_items')) {
            if (!Schema::hasColumn('cms_quotation_items', 'dimensions')) {
                Schema::table('cms_quotation_items', function (Blueprint $table) {
                    $table->string('dimensions', 100)->nullable()->after('line_total');
                });
            }
            if (!Schema::hasColumn('cms_quotation_items', 'dimensions_value')) {
                Schema::table('cms_quotation_items', function (Blueprint $table) {
                    $table->decimal('dimensions_value', 10, 4)->default(1)->after('dimensions');
                });
            }
        }
    }

    public function down(): void
    {
        Schema::table('cms_quotation_items', function (Blueprint $table) {
            $table->dropColumn(['dimensions', 'dimensions_value']);
        });
    }
};
