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
            $table->string('default_font')->nullable()->after('default_color');
            $table->json('customizations')->nullable()->after('default_font');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quick_invoice_profiles', function (Blueprint $table) {
            $table->dropColumn(['default_font', 'customizations']);
        });
    }
};