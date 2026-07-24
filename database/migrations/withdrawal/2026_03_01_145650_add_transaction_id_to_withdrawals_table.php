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
        Schema::table('withdrawals', function (Blueprint $table) {
            // Add transaction_id foreign key to link withdrawals to transactions table
            $table->unsignedBigInteger('transaction_id')->nullable()->after('id');
            
            // Add foreign key constraint
            $table->foreign('transaction_id')
                  ->references('id')
                  ->on('transactions')
                  ->onDelete('set null'); // If transaction deleted, keep withdrawal record
            
            // Add index for faster lookups
            $table->index('transaction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('withdrawals', function (Blueprint $table) {
            $table->dropForeign(['transaction_id']);
            $table->dropIndex(['transaction_id']);
            $table->dropColumn('transaction_id');
        });
    }
};
