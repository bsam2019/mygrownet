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
        Schema::table('transactions', function (Blueprint $table) {
            // Add transaction_source column to track which module generated the transaction
            $table->string('transaction_source', 50)
                ->nullable()
                ->after('transaction_type')
                ->comment('Module that generated this transaction (mygrownet, grownet, growbuilder, marketplace, etc.)');
            
            // Add module_reference for module-specific tracking
            $table->string('module_reference', 100)
                ->nullable()
                ->after('reference_number')
                ->comment('Module\'s internal reference ID');
            
            // Add index for performance
            $table->index('transaction_source');
            $table->index(['user_id', 'transaction_source']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'transaction_source']);
            $table->dropIndex(['transaction_source']);
            $table->dropColumn(['transaction_source', 'module_reference']);
        });
    }
};
