<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('growstream_creator_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creator_id')
                ->constrained('growstream_creator_profiles')
                ->cascadeOnDelete();
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('status', 20)->default('pending');
            $table->string('reference')->nullable();
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['creator_id', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('growstream_creator_payouts');
    }
};
