<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dead_letter_queue', function (Blueprint $table) {
            $table->id();
            $table->string('event_name');
            $table->json('payload');
            $table->text('error_message')->nullable();
            $table->string('error_class')->nullable();
            $table->string('queue')->nullable();
            $table->string('status')->default('pending');
            $table->integer('attempts')->default(0);
            $table->timestamp('failed_at')->useCurrent();
            $table->timestamps();
            $table->index(['status', 'failed_at']);
            $table->index('event_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dead_letter_queue');
    }
};
