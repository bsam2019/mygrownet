<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_fingerprints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('fingerprint_hash')->index();
            $table->string('user_agent');
            $table->string('ip_address');
            $table->json('device_info'); // Screen resolution, timezone, language, etc.
            $table->json('browser_info'); // Browser, version, plugins, etc.
            $table->boolean('is_trusted')->default(false);
            $table->timestamp('first_seen_at');
            $table->timestamp('last_seen_at');
            $table->timestamps();

            $table->unique(['user_id', 'fingerprint_hash']);
            $table->index(['ip_address', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_fingerprints');
    }
};