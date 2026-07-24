<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quick_invoice_custom_templates', function (Blueprint $table) {
            // Layout configuration stored as JSON
            $table->json('layout_json')->nullable()->after('table_style');
            
            // Field configuration (which fields are enabled/disabled)
            $table->json('field_config')->nullable()->after('layout_json');
            
            // Logo URL
            $table->string('logo_url')->nullable()->after('field_config');
            
            // Template version for tracking changes
            $table->integer('version')->default(1)->after('logo_url');
            
            // Template category (invoice, quote, receipt, etc.)
            $table->string('category')->default('invoice')->after('version');
            
            // Template tags for search/filtering
            $table->json('tags')->nullable()->after('category');
        });
    }

    public function down(): void
    {
        Schema::table('quick_invoice_custom_templates', function (Blueprint $table) {
            $table->dropColumn([
                'layout_json',
                'field_config',
                'logo_url',
                'version',
                'category',
                'tags',
            ]);
        });
    }
};
