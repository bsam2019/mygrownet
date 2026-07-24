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
        if (!Schema::hasTable('cms_growfinance_sync_config')) {

            Schema::create('cms_growfinance_sync_config', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_enabled')->default(false);
            $table->foreignId('growfinance_business_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('auto_sync')->default(true);
            $table->boolean('sync_invoices')->default(true);
            $table->boolean('sync_expenses')->default(true);
            $table->boolean('sync_payments')->default(true);
            $table->timestamps();
            
            $table->unique('company_id');
        });

        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_growfinance_sync_config');
    }
};
