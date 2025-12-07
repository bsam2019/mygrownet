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

        // Also create invoice_templates table if referenced
        if (!Schema::hasTable('growfinance_invoice_templates')) {
            Schema::create('growfinance_invoice_templates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('business_id')->constrained('users')->cascadeOnDelete();
                $table->string('name');
                $table->string('template_key')->unique();
                $table->json('settings')->nullable();
                $table->boolean('is_default')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['business_id', 'is_active']);
            });
        }

        // Also create team_members table if referenced
        if (!Schema::hasTable('growfinance_team_members')) {
            Schema::create('growfinance_team_members', function (Blueprint $table) {
                $table->id();
                $table->foreignId('business_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('role')->default('viewer'); // owner, admin, accountant, viewer
                $table->string('status')->default('active'); // active, inactive, pending
                $table->json('permissions')->nullable();
                $table->timestamp('invited_at')->nullable();
                $table->timestamp('accepted_at')->nullable();
                $table->timestamps();

                $table->unique(['business_id', 'user_id']);
                $table->index(['business_id', 'status']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('growfinance_team_members');
        Schema::dropIfExists('growfinance_invoice_templates');
        Schema::dropIfExists('growfinance_bank_accounts');
    }
};
