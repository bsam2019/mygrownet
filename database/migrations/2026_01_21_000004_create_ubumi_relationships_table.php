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
        Schema::create('ubumi_relationships', function (Blueprint $table) {
            $table->id();
            $table->uuid('person_id');
            $table->uuid('related_person_id');
            $table->enum('relationship_type', [
                'parent',
                'child',
                'sibling',
                'spouse',
                'grandparent',
                'grandchild',
                'aunt_uncle',
                'niece_nephew',
                'cousin',
                'guardian',
                'other'
            ]);
            $table->timestamps();

            $table->foreign('person_id')->references('id')->on('ubumi_persons')->onDelete('cascade');
            $table->foreign('related_person_id')->references('id')->on('ubumi_persons')->onDelete('cascade');
            
            $table->index('person_id');
            $table->index('related_person_id');
            $table->unique(['person_id', 'related_person_id', 'relationship_type'], 'unique_relationship');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ubumi_relationships');
    }
};
