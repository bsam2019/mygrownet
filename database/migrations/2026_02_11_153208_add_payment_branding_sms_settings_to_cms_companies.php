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
        // Add logo column for branding
        Schema::table('cms_companies', function (Blueprint $table) {
            if (!Schema::hasColumn('cms_companies', 'logo_path')) {
                $table->string('logo_path')->nullable()->after('industry');
            }
        });

        // Note: payment_instructions, branding, and sms settings will be stored in the existing 'settings' JSON column
        // This migration just ensures the logo_path column exists for file uploads
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cms_companies', function (Blueprint $table) {
            $table->dropColumn('logo_path');
        });
    }
};
