<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_identity_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('provider', 50);
            $table->string('provider_user_id');
            $table->string('provider_email')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'provider']);
            $table->unique(['provider', 'provider_user_id']);
            $table->index('provider');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_identity_links');
    }
};
