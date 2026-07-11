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
        // Bank accounts table - main purpose of this migration
        Schema::create('growfinance_bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('users')->cascadeOnDelete();
            $table->string('account_name');
            $table->string('account_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_branch')->nullable();
            $table->string('account_type')->default('checking'); // checking, savings, mobile_money
            $table->string('currency', 3)->default('ZMW');
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->decimal('current_balance', 15, 2)->default(0);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['business_id', 'is_active']);
        });

        // Note: growfinance_invoice_templates and growfinance_team_members are created
        // by the 2025_12_03_230000_create_growfinance_teams_table.php migration
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('growfinance_bank_accounts');
        // Note: growfinance_team_members and growfinance_invoice_templates are dropped
        // by the 2025_12_03_230000_create_growfinance_teams_table.php migration
    }
};
