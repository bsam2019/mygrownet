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
        Schema::create('agency_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->nullable()->constrained('agencies')->onDelete('cascade');
            $table->string('role_name');
            $table->boolean('is_system_role')->default(false);
            $table->json('permissions_json'); // Array of permission strings
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['agency_id', 'role_name']);
            $table->index('is_system_role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agency_roles');
    }
};
