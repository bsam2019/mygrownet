<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('growstream_creator_earnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creator_id')
                ->constrained('growstream_creator_profiles')
                ->cascadeOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->bigInteger('premium_watch_seconds')->default(0);
            $table->decimal('pool_amount', 15, 2)->default(0);
            $table->decimal('share_percentage', 5, 2)->default(0);
            $table->decimal('earned_amount', 15, 2)->default(0);
            $table->string('status', 20)->default('pending');
            $table->timestamps();

            $table->unique(['creator_id', 'period_start', 'period_end'], 'unique_creator_period');
            $table->index(['period_start', 'period_end']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('growstream_creator_earnings');
    }
};
