<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('growbuilder_ai_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('growbuilder_sites')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('action_type', 50); // content, style, page, navigation, etc.
            $table->string('section_type', 50)->nullable(); // hero, about, stats, etc.
            $table->string('business_type', 50)->nullable(); // restaurant, church, tutor, etc. (for industry learning)
            $table->boolean('applied')->default(false); // true if user applied, false if rejected
            $table->text('user_message')->nullable(); // what the user asked
            $table->text('ai_response')->nullable(); // what AI suggested (truncated)
            $table->json('context')->nullable(); // additional context
            $table->timestamps();
            
            // Indexes for quick lookups
            $table->index(['site_id', 'action_type']);
            $table->index(['site_id', 'applied']);
            $table->index(['business_type', 'applied']); // For industry-specific learning
            $table->index(['action_type', 'applied']); // For global learning
            $table->index(['section_type', 'applied']); // For content type learning
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('growbuilder_ai_feedback');
    }
};
