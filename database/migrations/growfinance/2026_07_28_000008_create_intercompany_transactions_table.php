<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('growfinance_intercompany_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('from_org_id');
            $table->unsignedBigInteger('to_org_id');
            $table->string('transaction_type');
            $table->string('reference')->nullable();
            $table->text('description')->nullable();
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('ZMW');
            $table->decimal('exchange_rate', 15, 6)->default(1.0);
            $table->json('mapping')->nullable();
            $table->string('status')->default('pending');
            $table->unsignedBigInteger('matched_transaction_id')->nullable();
            $table->timestamp('transaction_date');
            $table->timestamps();

            $table->foreign('from_org_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('to_org_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('matched_transaction_id')->references('id')->on('growfinance_intercompany_transactions')->onDelete('set null');
            $table->index(['from_org_id', 'to_org_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('growfinance_intercompany_transactions');
    }
};
