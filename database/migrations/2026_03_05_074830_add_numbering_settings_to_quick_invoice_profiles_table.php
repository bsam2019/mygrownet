<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quick_invoice_profiles', function (Blueprint $table) {
            // Numbering format settings for each document type
            $table->string('invoice_prefix', 20)->default('INV')->after('default_terms');
            $table->integer('invoice_next_number')->default(1)->after('invoice_prefix');
            $table->integer('invoice_number_padding')->default(4)->after('invoice_next_number'); // e.g., 0001
            
            $table->string('quotation_prefix', 20)->default('QUO')->after('invoice_number_padding');
            $table->integer('quotation_next_number')->default(1)->after('quotation_prefix');
            $table->integer('quotation_number_padding')->default(4)->after('quotation_next_number');
            
            $table->string('receipt_prefix', 20)->default('REC')->after('quotation_number_padding');
            $table->integer('receipt_next_number')->default(1)->after('receipt_prefix');
            $table->integer('receipt_number_padding')->default(4)->after('receipt_next_number');
            
            $table->string('delivery_note_prefix', 20)->default('DN')->after('receipt_number_padding');
            $table->integer('delivery_note_next_number')->default(1)->after('delivery_note_prefix');
            $table->integer('delivery_note_number_padding')->default(4)->after('delivery_note_next_number');
            
            // Template and color preferences (moved from document creation)
            $table->string('default_template', 50)->default('classic')->after('delivery_note_number_padding');
            $table->string('default_color', 7)->default('#2563eb')->after('default_template');
        });
    }

    public function down(): void
    {
        Schema::table('quick_invoice_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'invoice_prefix',
                'invoice_next_number',
                'invoice_number_padding',
                'quotation_prefix',
                'quotation_next_number',
                'quotation_number_padding',
                'receipt_prefix',
                'receipt_next_number',
                'receipt_number_padding',
                'delivery_note_prefix',
                'delivery_note_next_number',
                'delivery_note_number_padding',
                'default_template',
                'default_color',
            ]);
        });
    }
};
