<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sa_email_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sa_company_id')->constrained('sa_companies')->cascadeOnDelete();
            $table->string('provider')->default('smtp');
            $table->string('smtp_host')->nullable();
            $table->integer('smtp_port')->nullable();
            $table->string('smtp_username')->nullable();
            $table->text('smtp_password')->nullable();
            $table->string('smtp_encryption')->nullable();
            $table->string('from_address')->nullable();
            $table->string('from_name')->nullable();
            $table->boolean('verified')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sa_email_settings');
    }
};
