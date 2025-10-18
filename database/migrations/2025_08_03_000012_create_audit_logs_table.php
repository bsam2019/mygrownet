<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('event_type'); // investment_created, withdrawal_requested, etc.
            $table->string('auditable_type')->nullable(); // Model class name
            $table->unsignedBigInteger('auditable_id')->nullable(); // Model ID
            $table->json('old_values')->nullable(); // Previous values
            $table->json('new_values')->nullable(); // New values
            $table->string('ip_address');
            $table->string('user_agent')->nullable();
            $table->decimal('amount', 15, 2)->nullable(); // Financial amount if applicable
            $table->string('transaction_reference')->nullable();
            $table->json('metadata')->nullable(); // Additional context data
            $table->timestamps();

            $table->index(['auditable_type', 'auditable_id']);
            $table->index(['event_type', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['amount', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};