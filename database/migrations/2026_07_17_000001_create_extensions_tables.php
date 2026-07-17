<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('extensions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('version', 20)->nullable();
            $table->string('provider_class');
            $table->json('default_settings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('company_extensions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sa_company_id')->constrained('sa_companies')->cascadeOnDelete();
            $table->foreignId('extension_id')->constrained('extensions')->cascadeOnDelete();
            $table->string('status')->default('active');
            $table->json('settings')->nullable();
            $table->timestamp('subscribed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->unique(['sa_company_id', 'extension_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_extensions');
        Schema::dropIfExists('extensions');
    }
};
