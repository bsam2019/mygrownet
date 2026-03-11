<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('growstream_video_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->string('slug', 50)->unique();
            $table->integer('usage_count')->default(0);
            $table->timestamps();
            
            $table->index('name');
            $table->index('usage_count');
        });
        
        // Pivot table for video-tag relationship
        Schema::create('growstream_video_tag_pivot', function (Blueprint $table) {
            $table->foreignId('video_id')->constrained('growstream_videos')->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained('growstream_video_tags')->cascadeOnDelete();
            $table->timestamps();
            
            $table->primary(['video_id', 'tag_id']);
            $table->index('tag_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('growstream_video_tag_pivot');
        Schema::dropIfExists('growstream_video_tags');
    }
};
