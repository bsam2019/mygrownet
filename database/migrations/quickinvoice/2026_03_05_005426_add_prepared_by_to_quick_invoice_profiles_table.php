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
        Schema::table('quick_invoice_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('quick_invoice_profiles', 'prepared_by')) {
                $table->string('prepared_by')->nullable()->after('signature');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quick_invoice_profiles', function (Blueprint $table) {
            $table->dropColumn('prepared_by');
        });
    }
};
