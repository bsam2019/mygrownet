<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('message_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Template name (e.g., "Welcome Message")
            $table->string('subject'); // Message subject
            $table->text('body'); // Message body (can include placeholders like {name})
            $table->string('category')->default('general'); // general, announcement, reminder, etc.
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('message_templates');
    }
};
