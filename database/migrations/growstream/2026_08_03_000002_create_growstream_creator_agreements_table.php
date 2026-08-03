<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('growstream_creator_agreements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creator_profile_id')
                ->constrained('growstream_creator_profiles')
                ->cascadeOnDelete();
            $table->string('version', 20)->default('1.0');
            $table->boolean('accepted')->default(false);
            $table->text('agreement_snapshot')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();

            $table->index('creator_profile_id');
            $table->index('accepted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('growstream_creator_agreements');
    }
};
