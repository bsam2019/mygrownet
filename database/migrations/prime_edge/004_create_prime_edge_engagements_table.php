<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prime_edge_engagements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('client_id');
            $table->string('type');
            $table->text('description');
            $table->text('scope')->nullable();
            $table->string('status')->default('pending');
            $table->decimal('agreed_fee_amount', 12, 2)->nullable();
            $table->string('agreed_fee_currency', 3)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('client_id')->references('id')->on('prime_edge_clients')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prime_edge_engagements');
    }
};
