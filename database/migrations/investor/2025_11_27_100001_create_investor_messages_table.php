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
        if (Schema::hasTable('investor_messages')) {
            return;
        }

        Schema::create('investor_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investor_account_id')->constrained('investor_accounts')->onDelete('cascade');
            $table->foreignId('admin_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('subject');
            $table->text('content');
            $table->enum('direction', ['to_investor', 'from_investor'])->comment('to_investor = admin sent, from_investor = investor sent');
            $table->enum('status', ['unread', 'read', 'replied', 'archived'])->default('unread');
            $table->foreignId('parent_id')->nullable()->constrained('investor_messages')->onDelete('set null');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['investor_account_id', 'status']);
            $table->index(['direction', 'status']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investor_messages');
    }
};
