<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['payment', 'starter_kit', 'wallet', 'purchase'])->default('payment');
            $table->decimal('amount', 10, 2);
            $table->string('payment_method')->nullable();
            $table->string('transaction_reference')->nullable();
            $table->text('description')->nullable();
            $table->string('pdf_path')->nullable();
            $table->boolean('emailed')->default(false);
            $table->timestamp('emailed_at')->nullable();
            $table->json('metadata')->nullable(); // Store additional data
            $table->timestamps();
            
            $table->index(['user_id', 'type']);
            $table->index('receipt_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipts');
    }
};
