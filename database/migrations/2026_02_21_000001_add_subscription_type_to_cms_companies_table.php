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
        Schema::table('cms_companies', function (Blueprint $table) {
            $table->string('subscription_type')->default('paid')->after('status');
            // paid, sponsored, complimentary, partner
            
            $table->string('sponsor_reference')->nullable()->after('subscription_type');
            // Reference to who is sponsoring (e.g., "MyGrowNet", "Partner XYZ")
            
            $table->text('subscription_notes')->nullable()->after('sponsor_reference');
            // Internal notes about the subscription arrangement
            
            $table->timestamp('complimentary_until')->nullable()->after('subscription_notes');
            // For time-limited complimentary access
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cms_companies', function (Blueprint $table) {
            $table->dropColumn([
                'subscription_type',
                'sponsor_reference',
                'subscription_notes',
                'complimentary_until',
            ]);
        });
    }
};
