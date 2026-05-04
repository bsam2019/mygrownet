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
        Schema::create('cms_growfinance_sync_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('users')->cascadeOnDelete();
            $table->string('cms_entity_type', 50)->index();
            $table->unsignedBigInteger('cms_entity_id')->index();
            $table->foreignId('growfinance_journal_entry_id')->nullable()->constrained('growfinance_journal_entries')->nullOnDelete();
            $table->enum('sync_status', ['pending', 'synced', 'failed', 'skipped'])->default('pending')->index();
            $table->integer('sync_attempt_count')->default(0);
            $table->timestamp('last_sync_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
            
            $table->index(['cms_entity_type', 'cms_entity_id'], 'cms_entity_composite_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_growfinance_sync_log');
    }
};
