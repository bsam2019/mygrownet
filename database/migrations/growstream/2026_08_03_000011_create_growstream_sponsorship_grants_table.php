<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('growstream_sponsorship_grants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creator_id')
                ->constrained('growstream_creator_profiles')
                ->cascadeOnDelete();
            $table->string('title', 200);
            $table->text('description');
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('currency', 3)->default('ZMW');
            $table->json('milestones')->nullable();
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected', 'disbursed', 'completed'])
                ->default('submitted');
            $table->text('rejection_reason')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('allocated_at')->nullable();
            $table->timestamp('disbursed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['creator_id', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('growstream_sponsorship_grants');
    }
};
