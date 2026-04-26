<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // SQLite doesn't support ALTER COLUMN for enums, so we need to recreate the table
        if (Schema::hasTable('cms_audit_trail')) {
            // For SQLite, we need to use raw SQL to modify the CHECK constraint
            DB::statement('DROP TABLE IF EXISTS cms_audit_trail_backup');
            DB::statement('CREATE TABLE cms_audit_trail_backup AS SELECT * FROM cms_audit_trail');
            
            Schema::drop('cms_audit_trail');
            
            Schema::create('cms_audit_trail', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('cms_users');
                $table->string('entity_type', 100);
                $table->unsignedBigInteger('entity_id');
                $table->enum('action', [
                    'created', 
                    'updated', 
                    'deleted', 
                    'approved', 
                    'rejected', 
                    'locked', 
                    'unlocked', 
                    'sent', 
                    'converted', 
                    'cancelled', 
                    'voided',
                    'status_changed',  // New action type
                    'assigned',        // For job assignments
                    'completed',       // For job completion
                ]);
                $table->json('old_values')->nullable();
                $table->json('new_values')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->timestamp('created_at');
    
                $table->index(['company_id', 'created_at'], 'idx_company_date');
                $table->index(['entity_type', 'entity_id'], 'idx_entity');
                $table->index('user_id');
            });
            
            // Restore data
            DB::statement('INSERT INTO cms_audit_trail SELECT * FROM cms_audit_trail_backup');
            DB::statement('DROP TABLE cms_audit_trail_backup');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse by recreating with previous enum values
        if (Schema::hasTable('cms_audit_trail')) {
            DB::statement('DROP TABLE IF EXISTS cms_audit_trail_backup');
            DB::statement('CREATE TABLE cms_audit_trail_backup AS SELECT * FROM cms_audit_trail');
            
            Schema::drop('cms_audit_trail');
            
            Schema::create('cms_audit_trail', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('cms_users');
                $table->string('entity_type', 100);
                $table->unsignedBigInteger('entity_id');
                $table->enum('action', ['created', 'updated', 'deleted', 'approved', 'rejected', 'locked', 'unlocked', 'sent', 'converted', 'cancelled', 'voided']);
                $table->json('old_values')->nullable();
                $table->json('new_values')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->timestamp('created_at');
    
                $table->index(['company_id', 'created_at'], 'idx_company_date');
                $table->index(['entity_type', 'entity_id'], 'idx_entity');
                $table->index('user_id');
            });
            
            // Restore data (excluding new action types)
            DB::statement("INSERT INTO cms_audit_trail SELECT * FROM cms_audit_trail_backup WHERE action IN ('created', 'updated', 'deleted', 'approved', 'rejected', 'locked', 'unlocked', 'sent', 'converted', 'cancelled', 'voided')");
            DB::statement('DROP TABLE cms_audit_trail_backup');
        }
    }
};
