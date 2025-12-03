<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds a module field to notifications table to support module-specific
     * notifications (e.g., GrowFinance, GrowBiz, MyGrowNet).
     */
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            // Module identifier - allows filtering notifications by module
            // Examples: 'core', 'growfinance', 'growbiz', 'mygrownet'
            if (!Schema::hasColumn('notifications', 'module')) {
                $table->string('module', 50)->default('core')->after('type');
            }
            
            // Add index for module-based queries (using polymorphic columns)
            $table->index(['notifiable_type', 'notifiable_id', 'module'], 'notifications_notifiable_module_index');
            $table->index(['notifiable_type', 'notifiable_id', 'module', 'read_at'], 'notifications_notifiable_module_read_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('notifications_notifiable_module_read_index');
            $table->dropIndex('notifications_notifiable_module_index');
            if (Schema::hasColumn('notifications', 'module')) {
                $table->dropColumn('module');
            }
        });
    }
};
