<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('venture_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venture_id')->constrained('ventures')->onDelete('cascade');
            
            // Update Details
            $table->string('title');
            $table->text('content');
            $table->enum('type', [
                'milestone',
                'financial',
                'operational',
                'announcement',
                'alert',
                'general'
            ])->default('general');
            
            // Visibility
            $table->enum('visibility', ['public', 'investors_only', 'shareholders_only'])->default('investors_only');
            $table->boolean('send_notification')->default(true);
            $table->boolean('is_pinned')->default(false);
            
            // Metadata
            $table->integer('views_count')->default(0);
            $table->foreignId('posted_by')->constrained('users')->onDelete('restrict');
            $table->timestamp('published_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['venture_id', 'published_at']);
            $table->index(['type', 'visibility']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venture_updates');
    }
};
