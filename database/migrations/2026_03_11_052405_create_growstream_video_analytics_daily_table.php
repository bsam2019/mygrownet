<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('growstream_video_analytics_daily', function (Blueprint $table) {
            $table->id();
            $table->foreignId('video_id')->constrained('growstream_videos')->cascadeOnDelete();
            $table->date('date');
            
            // Metrics
            $table->integer('views')->default(0);
            $table->integer('unique_viewers')->default(0);
            $table->bigInteger('total_watch_time')->default(0)->comment('Total seconds watched');
            $table->integer('average_watch_duration')->default(0);
            $table->decimal('completion_rate', 5, 2)->default(0);
            $table->integer('likes')->default(0);
            $table->integer('shares')->default(0);
            $table->integer('bookmarks')->default(0);
            
            $table->timestamps();
            
            $table->unique(['video_id', 'date']);
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('growstream_video_analytics_daily');
    }
};
