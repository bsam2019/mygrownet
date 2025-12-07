<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('growfinance_expenses', function (Blueprint $table) {
            // Add receipt storage tracking fields if they don't exist
            if (!Schema::hasColumn('growfinance_expenses', 'receipt_size')) {
                $table->unsignedBigInteger('receipt_size')->nullable()->after('receipt_path');
            }
            if (!Schema::hasColumn('growfinance_expenses', 'receipt_original_name')) {
                $table->string('receipt_original_name')->nullable()->after('receipt_size');
            }
            if (!Schema::hasColumn('growfinance_expenses', 'receipt_mime_type')) {
                $table->string('receipt_mime_type', 100)->nullable()->after('receipt_original_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('growfinance_expenses', function (Blueprint $table) {
            $table->dropColumn(['receipt_size', 'receipt_original_name', 'receipt_mime_type']);
        });
    }
};
