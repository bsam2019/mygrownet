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
        if (!Schema::hasTable('cms_roles')) {
            Schema::create('cms_roles', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
                        $table->string('name', 100);
                        $table->json('permissions');
                        $table->json('approval_authority')->nullable();
                        $table->boolean('is_system_role')->default(false);
                        $table->timestamps();
            
                        $table->unique(['company_id', 'name'], 'unique_role_name');
                    });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_roles');
    }
};
