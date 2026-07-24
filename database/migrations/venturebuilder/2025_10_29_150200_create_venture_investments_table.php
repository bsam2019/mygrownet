<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('venture_investments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venture_id')->constrained('ventures')->onDelete('restrict');
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            
            // Investment Details
            $table->decimal('amount', 15, 2);
            $table->decimal('shares_allocated', 15, 6)->nullable();
            $table->decimal('equity_percentage', 5, 4)->nullable();
            
            // Status
            $table->enum('status', [
                'pending',
                'confirmed',
                'processing',
                'completed',
                'refunded',
                'cancelled'
            ])->default('pending');
            
            // Payment
            $table->enum('payment_method', ['wallet', 'mobile_money', 'bank_transfer'])->default('wallet');
            $table->string('payment_reference')->nullable();
            $table->timestamp('payment_confirmed_at')->nullable();
            
            // Shareholder Status
            $table->boolean('is_shareholder')->default(false);
            $table->date('shareholder_registered_at')->nullable();
            $table->string('shareholder_certificate_number')->nullable();
            
            // Metadata
            $table->text('notes')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('processed_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['venture_id', 'user_id']);
            $table->index(['user_id', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venture_investments');
    }
};
