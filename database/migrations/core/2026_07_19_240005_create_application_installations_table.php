<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_installations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('provisioning'); // provisioning | active | suspended
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->unique(['organization_id', 'application_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_installations');
    }
};
