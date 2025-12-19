<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Special contribution types defined by each group
        Schema::create('lifeplus_chilimba_contribution_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('lifeplus_chilimba_groups')->onDelete('cascade');
            $table->string('name'); // e.g., "Funeral", "Sickness", "Wedding", "Baby Shower"
            $table->string('icon')->default('💰'); // Emoji icon
            $table->decimal('default_amount', 10, 2)->nullable(); // Suggested amount
            $table->boolean('is_mandatory')->default(false); // Required for all members?
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['group_id', 'is_active']);
        });

        // Special contributions (separate from regular contributions)
        Schema::create('lifeplus_chilimba_special_contributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('lifeplus_chilimba_groups')->onDelete('cascade');
            $table->foreignId('type_id')->constrained('lifeplus_chilimba_contribution_types')->onDelete('cascade');
            $table->foreignId('member_id')->constrained('lifeplus_chilimba_members')->onDelete('cascade');
            $table->foreignId('recorded_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('beneficiary_id')->nullable()->constrained('lifeplus_chilimba_members')->onDelete('set null');
            $table->string('beneficiary_name')->nullable(); // If beneficiary is not a member
            $table->date('contribution_date');
            $table->decimal('amount', 10, 2);
            $table->enum('payment_method', ['cash', 'mobile_money'])->default('cash');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['group_id', 'type_id', 'contribution_date'], 'lp_special_contrib_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lifeplus_chilimba_special_contributions');
        Schema::dropIfExists('lifeplus_chilimba_contribution_types');
    }
};
