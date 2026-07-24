<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('growfinance_journal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('users')->cascadeOnDelete();
            $table->string('entry_number')->unique();
            $table->date('entry_date');
            $table->text('description')->nullable();
            $table->string('reference')->nullable();
            $table->boolean('is_posted')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['business_id', 'entry_date']);
            $table->index(['business_id', 'is_posted']);
        });

        Schema::create('growfinance_journal_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_entry_id')->constrained('growfinance_journal_entries')->cascadeOnDelete();
            $table->foreignId('account_id')->constrained('growfinance_accounts')->cascadeOnDelete();
            $table->decimal('debit_amount', 15, 2)->default(0);
            $table->decimal('credit_amount', 15, 2)->default(0);
            $table->string('description')->nullable();
            $table->timestamps();

            $table->index('journal_entry_id');
            $table->index('account_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('growfinance_journal_lines');
        Schema::dropIfExists('growfinance_journal_entries');
    }
};
