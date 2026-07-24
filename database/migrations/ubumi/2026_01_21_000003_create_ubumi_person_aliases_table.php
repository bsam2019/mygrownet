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
        Schema::create('ubumi_person_aliases', function (Blueprint $table) {
            $table->id();
            $table->uuid('person_id');
            $table->string('alias_name');
            $table->enum('alias_type', ['nickname', 'spelling_variant', 'clan_name', 'original_name']);
            $table->timestamps();

            $table->foreign('person_id')->references('id')->on('ubumi_persons')->onDelete('cascade');
            
            $table->index('person_id');
            $table->index('alias_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ubumi_person_aliases');
    }
};
