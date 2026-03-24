<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bizdocs_user_template_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('bizdocs_business_profiles')->onDelete('cascade');
            $table->string('document_type');
            $table->foreignId('template_id')->constrained('bizdocs_document_templates')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['business_id', 'document_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bizdocs_user_template_preferences');
    }
};
