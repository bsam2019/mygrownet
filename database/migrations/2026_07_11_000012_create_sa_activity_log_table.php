<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sa_activity_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sa_company_id')->constrained('sa_companies')->cascadeOnDelete();
            $table->unsignedBigInteger('actor_user_id')->nullable();
            $table->string('context'); // inventory, purchasing, sales, audit
            $table->string('event_name'); // SaleCompleted, PurchaseOrderReceived, etc.
            $table->string('subject_type')->nullable(); // e.g., Sale, PurchaseOrder
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->text('description');
            $table->json('payload')->nullable();
            $table->timestamp('occurred_at');

            $table->index(['sa_company_id', 'occurred_at']);
            $table->index(['sa_company_id', 'context']);
            $table->index(['sa_company_id', 'event_name']);
            $table->index('actor_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sa_activity_log');
    }
};
