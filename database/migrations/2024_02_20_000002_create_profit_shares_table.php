<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profit_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investment_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->decimal('percentage', 5, 2);
            $table->enum('status', ['pending', 'processed', 'failed'])->default('pending');
            $table->string('description')->nullable();
            $table->timestamp('payout_date')->nullable();
            $table->timestamps();

            $table->index(['investment_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index('payout_date');
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('profit_shares');
        Schema::enableForeignKeyConstraints();
    }
};