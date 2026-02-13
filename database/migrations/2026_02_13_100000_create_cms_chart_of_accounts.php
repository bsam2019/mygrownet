<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Chart of Accounts
        Schema::create('cms_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
            $table->string('code', 20)->index();
            $table->string('name');
            $table->enum('type', ['asset', 'liability', 'equity', 'income', 'expense']);
            $table->string('category', 100)->nullable();
            $table->text('description')->nullable();
            $table->decimal('current_balance', 15, 2)->default(0);
            $table->boolean('is_system')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique(['company_id', 'code']);
            $table->index(['company_id', 'type']);
        });

        // Journal Entries
        Schema::create('cms_journal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
            $table->string('entry_number', 50)->unique();
            $table->date('entry_date');
            $table->text('description');
            $table->string('reference', 100)->nullable();
            $table->boolean('is_posted')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('cms_users')->nullOnDelete();
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();
            
            $table->index(['company_id', 'entry_date']);
            $table->index(['company_id', 'is_posted']);
        });

        // Journal Lines
        Schema::create('cms_journal_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_entry_id')->constrained('cms_journal_entries')->cascadeOnDelete();
            $table->foreignId('account_id')->constrained('cms_accounts')->cascadeOnDelete();
            $table->decimal('debit_amount', 15, 2)->default(0);
            $table->decimal('credit_amount', 15, 2)->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->index('journal_entry_id');
            $table->index('account_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_journal_lines');
        Schema::dropIfExists('cms_journal_entries');
        Schema::dropIfExists('cms_accounts');
    }
};
