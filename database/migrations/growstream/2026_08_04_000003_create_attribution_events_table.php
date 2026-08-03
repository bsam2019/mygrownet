<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('growstream_attribution_events', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('creator_id')->constrained('growstream_creator_profiles')->cascadeOnDelete();
            $table->string('source', 60)->nullable();
            $table->string('visitor_session_id', 64)->index();
            $table->foreignId('converted_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('watch_minutes_attributed')->default(0);
            $table->timestamps();

            $table->index(['creator_id', 'source']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('growstream_attribution_events');
    }
};
