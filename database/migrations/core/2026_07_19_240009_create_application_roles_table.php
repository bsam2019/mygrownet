<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->string('role_name');
            $table->json('permissions');
            $table->timestamps();
            $table->unique(['application_id', 'role_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_roles');
    }
};
