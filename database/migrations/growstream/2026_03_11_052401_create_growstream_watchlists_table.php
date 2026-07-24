<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('growstream_watchlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->morphs('watchlistable'); // video or series
            $table->timestamp('added_at');
            
            $table->unique(['user_id', 'watchlistable_type', 'watchlistable_id'], 'unique_watchlist_item');
            $table->index(['user_id', 'added_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('growstream_watchlists');
    }
};
