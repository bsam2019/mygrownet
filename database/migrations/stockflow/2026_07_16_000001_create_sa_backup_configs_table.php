<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sa_backup_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sa_company_id')->constrained('sa_companies')->cascadeOnDelete();
            $table->string('email');
            $table->boolean('enabled')->default(false);
            $table->string('frequency')->default('daily');
            $table->string('include_files')->default('database');
            $table->timestamp('last_backup_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sa_backup_configs');
    }
};
