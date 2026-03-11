<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('growstream_video_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->text('description')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('growstream_video_categories')->cascadeOnDelete();
            $table->string('icon', 50)->nullable();
            $table->string('color', 7)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('parent_id');
            $table->index(['is_active', 'sort_order']);
        });
        
        // Pivot table for video-category relationship
        Schema::create('growstream_video_category_pivot', function (Blueprint $table) {
            $table->foreignId('video_id')->constrained('growstream_videos')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('growstream_video_categories')->cascadeOnDelete();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            
            $table->primary(['video_id', 'category_id']);
            $table->index('category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('growstream_video_category_pivot');
        Schema::dropIfExists('growstream_video_categories');
    }
};
