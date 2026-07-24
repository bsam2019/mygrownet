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
        Schema::create('agency_client_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained('agencies')->onDelete('cascade');
            $table->string('name');
            $table->string('color')->default('#3b82f6');
            $table->timestamps();
            
            $table->unique(['agency_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agency_client_tags');
    }
};
