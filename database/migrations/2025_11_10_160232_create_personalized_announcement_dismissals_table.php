<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('personalized_announcement_dismissals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('announcement_key'); // e.g., 'tier_progress', 'starter_kit', 'earnings_milestone_500'
            $table->timestamp('dismissed_at');
            $table->timestamp('expires_at')->nullable(); // When dismissal expires (e.g., 7 days)
            
            $table->unique(['user_id', 'announcement_key'], 'pa_dismissals_user_key_unique');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personalized_announcement_dismissals');
    }
};
