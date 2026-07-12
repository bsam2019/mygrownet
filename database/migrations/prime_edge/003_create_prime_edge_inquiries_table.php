<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prime_edge_inquiries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('client_id');
            $table->text('service_description');
            $table->string('preferred_service_type')->nullable();
            $table->string('status')->default('new');
            $table->decimal('quoted_amount', 12, 2)->nullable();
            $table->string('quoted_currency', 3)->nullable();
            $table->text('quote_notes')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('quoted_at')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->foreign('client_id')->references('id')->on('prime_edge_clients')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prime_edge_inquiries');
    }
};
