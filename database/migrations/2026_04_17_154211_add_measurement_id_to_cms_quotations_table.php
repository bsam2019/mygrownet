<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cms_quotations', function (Blueprint $table) {
            $table->foreignId('measurement_id')->nullable()->after('customer_id')->constrained('cms_measurement_records')->onDelete('set null');
            $table->index('measurement_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cms_quotations', function (Blueprint $table) {
            $table->dropForeign(['measurement_id']);
            $table->dropColumn('measurement_id');
        });
    }
};
