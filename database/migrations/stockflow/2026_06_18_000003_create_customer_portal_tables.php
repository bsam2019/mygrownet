<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pivot table linking Users to CMS Customers for portal access
        if (!Schema::hasTable('portal_user_customers')) {
            Schema::create('portal_user_customers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('customer_id')->constrained('cms_customers')->cascadeOnDelete();
                $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
                $table->boolean('is_active')->default(true);
                $table->timestamp('last_login_at')->nullable();
                $table->timestamps();

                $table->unique(['user_id', 'customer_id']);
                $table->index(['user_id', 'is_active']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('portal_user_customers');
    }
};
