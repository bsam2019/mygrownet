<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('growstream_video_rentals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('video_id')->constrained('growstream_videos')->cascadeOnDelete();
            $table->decimal('price', 15, 2)->default(0);
            $table->string('currency', 3)->default('ZMW');
            $table->string('access_duration', 20)->default('48_hours');
            $table->timestamp('granted_at');
            $table->timestamp('expires_at');
            $table->string('provider_reference')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->unique(['user_id', 'video_id', 'granted_at']);
            $table->index(['user_id', 'status']);
            $table->index(['video_id', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('growstream_video_rentals');
    }
};
