<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Locations for multi-location businesses
        Schema::create('bizboost_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('bizboost_businesses')->onDelete('cascade');
            $table->string('name');
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('phone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->json('business_hours')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['business_id', 'is_active']);
        });

        // Team members
        Schema::create('bizboost_team_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('bizboost_businesses')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name');
            $table->string('email');
            $table->string('role')->default('member'); // owner, admin, editor, member
            $table->json('permissions')->nullable();
            $table->foreignId('location_id')->nullable()->constrained('bizboost_locations')->onDelete('set null');
            $table->string('status')->default('pending'); // pending, active, inactive
            $table->timestamp('invited_at')->nullable();
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();
            
            $table->unique(['business_id', 'email']);
            $table->index(['business_id', 'status']);
        });

        // Team invitations
        Schema::create('bizboost_team_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('bizboost_businesses')->onDelete('cascade');
            $table->foreignId('team_member_id')->constrained('bizboost_team_members')->onDelete('cascade');
            $table->string('token')->unique();
            $table->string('code', 8)->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();
            
            $table->index(['token', 'expires_at']);
            $table->index(['code', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bizboost_team_invitations');
        Schema::dropIfExists('bizboost_team_members');
        Schema::dropIfExists('bizboost_locations');
    }
};
