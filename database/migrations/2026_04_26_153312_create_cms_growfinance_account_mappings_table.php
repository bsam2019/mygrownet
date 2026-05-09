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
        if (!Schema::hasTable('cms_growfinance_account_mappings')) {

            Schema::create('cms_growfinance_account_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('users')->cascadeOnDelete();
            $table->enum('cms_entity_type', ['invoice', 'expense', 'payment'])->index();
            $table->string('cms_category', 100)->nullable();
            $table->string('cms_payment_method', 50)->nullable();
            $table->foreignId('growfinance_account_id')->constrained('growfinance_accounts')->cascadeOnDelete();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            
            $table->unique(['company_id', 'cms_entity_type', 'cms_category', 'cms_payment_method'], 'unique_mapping');
        });

        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_growfinance_account_mappings');
    }
};
