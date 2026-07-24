<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('starter_kit_gifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gifter_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('recipient_id')->constrained('users')->onDelete('cascade');
            $table->string('tier'); // basic or premium
            $table->decimal('amount', 10, 2);
            $table->foreignId('purchase_id')->nullable()->constrained('starter_kit_purchases');
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['gifter_id', 'created_at']);
            $table->index(['recipient_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('starter_kit_gifts');
    }
};
