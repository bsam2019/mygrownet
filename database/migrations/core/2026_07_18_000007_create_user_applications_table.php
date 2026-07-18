<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->string('relationship_type'); // member, customer, admin, manager
            $table->string('status')->default('active');
            $table->timestamps();

            $table->unique(['user_id', 'application_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_applications');
    }
};
