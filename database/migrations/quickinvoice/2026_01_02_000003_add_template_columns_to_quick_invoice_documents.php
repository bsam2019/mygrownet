<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quick_invoice_documents', function (Blueprint $table) {
            if (!Schema::hasColumn('quick_invoice_documents', 'template')) {
                $table->string('template')->default('classic')->after('status');
            }
            if (!Schema::hasColumn('quick_invoice_documents', 'colors')) {
                $table->json('colors')->nullable()->after('template');
            }
            if (!Schema::hasColumn('quick_invoice_documents', 'signature')) {
                $table->string('signature')->nullable()->after('colors');
            }
        });
    }

    public function down(): void
    {
        Schema::table('quick_invoice_documents', function (Blueprint $table) {
            $table->dropColumn(['template', 'colors', 'signature']);
        });
    }
};
