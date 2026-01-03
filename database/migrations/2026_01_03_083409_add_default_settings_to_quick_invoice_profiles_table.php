<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quick_invoice_profiles', function (Blueprint $table) {
            $table->decimal('default_tax_rate', 5, 2)->default(0)->after('signature');
            $table->decimal('default_discount_rate', 5, 2)->default(0)->after('default_tax_rate');
            $table->text('default_notes')->nullable()->after('default_discount_rate');
            $table->text('default_terms')->nullable()->after('default_notes');
        });
    }

    public function down(): void
    {
        Schema::table('quick_invoice_profiles', function (Blueprint $table) {
            $table->dropColumn(['default_tax_rate', 'default_discount_rate', 'default_notes', 'default_terms']);
        });
    }
};
