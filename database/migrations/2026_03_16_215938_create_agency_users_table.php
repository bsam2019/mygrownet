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
        Schema::create('agency_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained('agencies')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('role_id')->constrained('agency_roles')->onDelete('restrict');
            $table->enum('status', ['active', 'inactive', 'invited'])->default('invited');
            $table->foreignId('invited_by')->nullable()->constrained('users');
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->unique(['agency_id', 'user_id']);
            $table->index(['agency_id', 'status']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agency_users');
    }
};
