<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('venture_share_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venture_id')->constrained('ventures')->onDelete('restrict');
            $table->foreignId('from_user_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('to_user_id')->constrained('users')->onDelete('restrict');
            $table->decimal('shares', 15, 6);
            $table->decimal('price_per_share', 15, 2)->nullable()->comment('Price per share (null = gift/transfer without sale)');
            $table->decimal('total_value', 15, 2)->nullable()->comment('Total transfer value (shares * price_per_share)');
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed', 'cancelled'])->default('pending');
            $table->text('reason')->nullable();
            $table->text('admin_notes')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['venture_id', 'status']);
            $table->index(['from_user_id', 'status']);
            $table->index(['to_user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venture_share_transfers');
    }
};
