<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lgr_manual_awards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('awarded_by')->constrained('users')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->string('award_type')->default('early_adopter'); // early_adopter, performance, special
            $table->text('reason');
            $table->boolean('credited')->default(false);
            $table->timestamp('credited_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'created_at']);
            $table->index('awarded_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lgr_manual_awards');
    }
};
