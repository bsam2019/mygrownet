<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_outbox', function (Blueprint $table) {
            $table->id();
            $table->string('event_name');
            $table->json('payload');
            $table->json('context')->nullable();
            $table->string('publisher');
            $table->string('status')->default('pending')->index();
            $table->integer('attempts')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('published_at')->nullable();
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_outbox');
    }
};
