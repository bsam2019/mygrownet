<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organization_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invited_user_id')->nullable()->constrained('users');
            $table->string('email')->nullable();
            $table->string('role');
            $table->string('token')->unique();
            $table->timestamp('expires_at');
            $table->string('status')->default('pending'); // pending | accepted | expired | revoked
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_invitations');
    }
};
