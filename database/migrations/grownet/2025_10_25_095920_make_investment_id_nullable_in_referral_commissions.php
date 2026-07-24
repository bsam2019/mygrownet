<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('referral_commissions', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['investment_id']);
            
            // Make investment_id nullable
            $table->foreignId('investment_id')->nullable()->change();
            
            // Re-add the foreign key constraint (nullable)
            $table->foreign('investment_id')
                  ->references('id')
                  ->on('investments')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('referral_commissions', function (Blueprint $table) {
            // Drop the nullable foreign key
            $table->dropForeign(['investment_id']);
            
            // Make it required again
            $table->foreignId('investment_id')->nullable(false)->change();
            
            // Re-add the foreign key constraint (required)
            $table->foreign('investment_id')
                  ->references('id')
                  ->on('investments')
                  ->onDelete('cascade');
        });
    }
};
