<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cms_invoices', function (Blueprint $table) {
            $table->foreignId('recurring_invoice_id')->nullable()->after('job_id')->constrained('cms_recurring_invoices')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('cms_invoices', function (Blueprint $table) {
            $table->dropForeign(['recurring_invoice_id']);
            $table->dropColumn('recurring_invoice_id');
        });
    }
};
