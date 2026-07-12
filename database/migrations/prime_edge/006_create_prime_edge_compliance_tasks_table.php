<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prime_edge_compliance_tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('client_id');
            $table->string('type');
            $table->text('description');
            $table->date('due_date');
            $table->string('recurrence')->default('one_off');
            $table->string('status')->default('pending');
            $table->integer('reminder_days')->default(7);
            $table->text('notes')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('reminded_at')->nullable();
            $table->timestamps();

            $table->foreign('client_id')->references('id')->on('prime_edge_clients')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prime_edge_compliance_tasks');
    }
};
