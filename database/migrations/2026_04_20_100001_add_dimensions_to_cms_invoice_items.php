<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cms_invoice_items', function (Blueprint $table) {
            $table->string('dimensions', 100)->nullable()->after('amount');
            $table->decimal('dimensions_value', 10, 4)->default(1)->after('dimensions');
            // Add line_total as alias (amount column already exists)
            $table->decimal('line_total', 15, 2)->default(0)->after('dimensions_value');
        });
    }

    public function down(): void
    {
        Schema::table('cms_invoice_items', function (Blueprint $table) {
            $table->dropColumn(['dimensions', 'dimensions_value', 'line_total']);
        });
    }
};
