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
        Schema::create('cms_sync_log', function (Blueprint $table) {
            $table->id();
            $table->string('cms_entity_type', 50); // 'expense', 'budget', etc.
            $table->unsignedBigInteger('cms_entity_id');
            $table->unsignedBigInteger('transaction_id')->nullable();
            $table->enum('sync_status', ['pending', 'synced', 'failed'])->default('pending');
            $table->text('sync_error')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['cms_entity_type', 'cms_entity_id'], 'idx_cms_entity');
            $table->index('transaction_id', 'idx_transaction');
            $table->index('sync_status', 'idx_sync_status');
            
            // Foreign key
            $table->foreign('transaction_id')
                  ->references('id')
                  ->on('transactions')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_sync_log');
    }
};
