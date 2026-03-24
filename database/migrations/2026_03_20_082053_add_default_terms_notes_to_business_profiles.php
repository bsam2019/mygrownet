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
        Schema::table('bizdocs_business_profiles', function (Blueprint $table) {
            $table->text('default_terms')->nullable()->after('default_tax_rate');
            $table->text('default_notes')->nullable()->after('default_terms');
            $table->text('default_payment_instructions')->nullable()->after('default_notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bizdocs_business_profiles', function (Blueprint $table) {
            $table->dropColumn(['default_terms', 'default_notes', 'default_payment_instructions']);
        });
    }
};
