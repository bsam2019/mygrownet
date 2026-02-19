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
        if (!Schema::hasTable('cms_audit_trail')) {
            Schema::create('cms_audit_trail', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
                        $table->foreignId('user_id')->constrained('cms_users');
                        $table->string('entity_type', 100);
                        $table->unsignedBigInteger('entity_id');
                        $table->enum('action', ['created', 'updated', 'deleted', 'approved', 'rejected', 'locked', 'unlocked']);
                        $table->json('old_values')->nullable();
                        $table->json('new_values')->nullable();
                        $table->string('ip_address', 45)->nullable();
                        $table->timestamp('created_at');
            
                        $table->index(['company_id', 'created_at'], 'idx_company_date');
                        $table->index(['entity_type', 'entity_id'], 'idx_entity');
                        $table->index('user_id');
                    });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_audit_trail');
    }
};
