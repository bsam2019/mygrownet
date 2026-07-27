<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('plan_id')->constrained('subscription_plans')->onDelete('restrict');
            $table->decimal('amount', 10, 2);
            $table->string('status')->default('pending'); // pending, trial, active, expired, cancelled, suspended
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->timestamp('renewal_date')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason')->nullable();
            $table->boolean('auto_renew')->default(true);
            $table->boolean('is_trial')->default(false);
            $table->integer('trial_days')->default(0);
            $table->integer('failure_count')->default(0);
            $table->timestamps();

            $table->index('user_id');
            $table->index('status');
            $table->index('renewal_date');
        });

        Schema::create('billing_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained('billing_subscriptions')->onDelete('cascade');
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->string('invoice_number')->unique();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('ZMW');
            $table->string('status')->default('draft'); // draft, issued, paid, overdue, cancelled, refunded
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('due_date');
            $table->timestamp('paid_at')->nullable();
            $table->text('description')->nullable();
            $table->json('line_items')->nullable();
            $table->timestamps();

            $table->index('organization_id');
            $table->index('status');
            $table->index('due_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_invoices');
        Schema::dropIfExists('billing_subscriptions');
    }
};
