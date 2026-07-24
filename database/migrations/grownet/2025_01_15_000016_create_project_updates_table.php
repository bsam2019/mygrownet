<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_project_id')->constrained()->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('content');
            $table->enum('update_type', ['progress', 'milestone', 'financial', 'announcement', 'issue'])->default('progress');
            $table->json('attachments')->nullable(); // Images, documents, etc.
            $table->boolean('notify_contributors')->default(true);
            $table->boolean('is_public')->default(true);
            $table->timestamps();

            $table->index(['community_project_id', 'update_type']);
            $table->index(['created_at', 'is_public']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_updates');
    }
};