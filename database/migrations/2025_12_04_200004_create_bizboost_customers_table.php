<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bizboost_customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('bizboost_businesses')->onDelete('cascade');
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('whatsapp')->nullable();
            $table->text('address')->nullable();
            $table->text('notes')->nullable();
            $table->string('source')->nullable(); // walk-in, referral, social, etc.
            $table->date('birthday')->nullable();
            $table->decimal('total_spent', 12, 2)->default(0);
            $table->integer('total_orders')->default(0);
            $table->timestamp('last_purchase_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['business_id', 'is_active']);
            $table->index(['business_id', 'phone']);
            $table->index(['business_id', 'email']);
        });

        Schema::create('bizboost_customer_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('bizboost_businesses')->onDelete('cascade');
            $table->string('name');
            $table->string('color')->default('#6366f1');
            $table->timestamps();
            
            $table->unique(['business_id', 'name']);
        });

        Schema::create('bizboost_customer_tag_pivot', function (Blueprint $table) {
            $table->foreignId('customer_id')->constrained('bizboost_customers')->onDelete('cascade');
            $table->foreignId('tag_id')->constrained('bizboost_customer_tags')->onDelete('cascade');
            
            $table->primary(['customer_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bizboost_customer_tag_pivot');
        Schema::dropIfExists('bizboost_customer_tags');
        Schema::dropIfExists('bizboost_customers');
    }
};
