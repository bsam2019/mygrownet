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
        Schema::create('agency_client_tag_map', function (Blueprint $table) {
            $table->foreignId('client_id')->constrained('agency_clients')->onDelete('cascade');
            $table->foreignId('tag_id')->constrained('agency_client_tags')->onDelete('cascade');
            $table->timestamps();
            
            $table->primary(['client_id', 'tag_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agency_client_tag_map');
    }
};
