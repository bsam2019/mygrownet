<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('growstream_creator_tips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('creator_id')->constrained('growstream_creator_profiles')->cascadeOnDelete();
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('currency', 3)->default('ZMW');
            $table->text('message')->nullable();
            $table->boolean('is_anonymous')->default(false);
            $table->string('provider_reference')->nullable();
            $table->string('status', 20)->default('completed');
            $table->timestamps();

            $table->index(['creator_id', 'created_at']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('growstream_creator_tips');
    }
};
