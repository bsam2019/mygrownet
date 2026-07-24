<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('growbuilder_ai_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('site_id')->nullable()->constrained('growbuilder_sites')->onDelete('set null');
            $table->string('prompt_type', 50); // content, seo, section, etc.
            $table->text('prompt')->nullable();
            $table->integer('tokens_used')->default(0);
            $table->string('month_year', 7); // Format: 2025-12
            $table->string('model', 100)->nullable();
            $table->boolean('is_priority')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'month_year']);
            $table->index('prompt_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('growbuilder_ai_usage');
    }
};
