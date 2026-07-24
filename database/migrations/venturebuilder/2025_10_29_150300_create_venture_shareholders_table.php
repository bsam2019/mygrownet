<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('venture_shareholders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venture_id')->constrained('ventures')->onDelete('restrict');
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('investment_id')->constrained('venture_investments')->onDelete('restrict');
            
            // Shareholding Details
            $table->decimal('total_investment', 15, 2);
            $table->decimal('shares_owned', 15, 6);
            $table->decimal('equity_percentage', 5, 4);
            
            // Legal Details
            $table->string('certificate_number')->unique();
            $table->date('registration_date');
            $table->string('shareholder_agreement_path')->nullable();
            $table->boolean('agreement_signed')->default(false);
            $table->timestamp('agreement_signed_at')->nullable();
            
            // Status
            $table->enum('status', ['active', 'transferred', 'inactive'])->default('active');
            
            // Dividends
            $table->decimal('total_dividends_received', 15, 2)->default(0);
            $table->timestamp('last_dividend_date')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['venture_id', 'user_id']);
            $table->index('certificate_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venture_shareholders');
    }
};
