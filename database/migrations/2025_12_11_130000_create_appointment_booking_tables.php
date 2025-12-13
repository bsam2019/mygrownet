<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Services catalog - what services the business offers
        Schema::create('growbiz_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Business owner
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('duration_minutes')->default(60);
            $table->integer('buffer_minutes')->default(0); // Buffer time after appointment
            $table->decimal('price', 12, 2)->default(0);
            $table->string('currency', 3)->default('ZMW');
            $table->string('category')->nullable();
            $table->string('color', 7)->default('#3b82f6'); // For calendar display
            $table->boolean('is_active')->default(true);
            $table->boolean('allow_online_booking')->default(true);
            $table->integer('max_bookings_per_slot')->default(1); // For group services
            $table->json('required_resources')->nullable(); // Equipment/room needed
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'is_active']);
            $table->index(['user_id', 'category']);
        });

        // Staff members who can provide services
        Schema::create('growbiz_service_providers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Business owner
            $table->foreignId('employee_id')->nullable()->constrained('growbiz_employees')->onDelete('set null');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('bio')->nullable();
            $table->string('avatar')->nullable();
            $table->string('color', 7)->default('#10b981'); // For calendar display
            $table->boolean('is_active')->default(true);
            $table->boolean('accept_online_bookings')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'is_active']);
        });

        // Which services each provider can offer
        Schema::create('growbiz_service_provider_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('growbiz_service_providers')->onDelete('cascade');
            $table->foreignId('service_id')->constrained('growbiz_services')->onDelete('cascade');
            $table->decimal('custom_price', 12, 2)->nullable(); // Override service price
            $table->integer('custom_duration')->nullable(); // Override duration
            $table->timestamps();

            $table->unique(['provider_id', 'service_id']);
        });

        // Business hours / availability schedule
        Schema::create('growbiz_availability_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('provider_id')->nullable()->constrained('growbiz_service_providers')->onDelete('cascade');
            $table->tinyInteger('day_of_week'); // 0=Sunday, 6=Saturday
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_available')->default(true);
            $table->timestamps();

            $table->index(['user_id', 'day_of_week']);
            $table->index(['provider_id', 'day_of_week']);
        });

        // Special dates (holidays, closures, special hours)
        Schema::create('growbiz_availability_exceptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('provider_id')->nullable()->constrained('growbiz_service_providers')->onDelete('cascade');
            $table->date('date');
            $table->enum('type', ['closed', 'modified_hours', 'extra_availability']);
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('reason')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'date']);
            $table->index(['provider_id', 'date']);
        });

        // Customers/clients for appointments
        Schema::create('growbiz_booking_customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Business owner
            $table->foreignId('registered_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('notes')->nullable();
            $table->json('preferences')->nullable();
            $table->integer('total_bookings')->default(0);
            $table->integer('no_shows')->default(0);
            $table->integer('cancellations')->default(0);
            $table->timestamp('last_visit_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'email']);
            $table->index(['user_id', 'phone']);
        });

        // Main appointments table
        Schema::create('growbiz_appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Business owner
            $table->foreignId('service_id')->constrained('growbiz_services')->onDelete('cascade');
            $table->foreignId('provider_id')->nullable()->constrained('growbiz_service_providers')->onDelete('set null');
            $table->foreignId('customer_id')->nullable()->constrained('growbiz_booking_customers')->onDelete('set null');
            $table->string('booking_reference', 20)->unique();
            $table->date('appointment_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('duration_minutes');
            $table->enum('status', [
                'pending',      // Awaiting confirmation
                'confirmed',    // Confirmed by business
                'in_progress',  // Currently happening
                'completed',    // Successfully completed
                'cancelled',    // Cancelled by either party
                'no_show',      // Customer didn't show up
                'rescheduled'   // Moved to different time
            ])->default('pending');
            $table->decimal('price', 12, 2)->default(0);
            $table->string('currency', 3)->default('ZMW');
            $table->enum('payment_status', ['unpaid', 'partial', 'paid', 'refunded'])->default('unpaid');
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->text('customer_notes')->nullable(); // Notes from customer
            $table->text('internal_notes')->nullable(); // Internal staff notes
            $table->enum('booking_source', ['online', 'phone', 'walk_in', 'app', 'referral'])->default('online');
            $table->string('cancellation_reason')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('invoice_id')->nullable(); // Link to GrowFinance invoice
            $table->boolean('reminder_sent')->default(false);
            $table->boolean('follow_up_sent')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'appointment_date']);
            $table->index(['user_id', 'status']);
            $table->index(['provider_id', 'appointment_date']);
            $table->index(['customer_id', 'appointment_date']);
            $table->index('booking_reference');
        });

        // Recurring appointment patterns
        Schema::create('growbiz_recurring_appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_id')->constrained('growbiz_services')->onDelete('cascade');
            $table->foreignId('provider_id')->nullable()->constrained('growbiz_service_providers')->onDelete('set null');
            $table->foreignId('customer_id')->nullable()->constrained('growbiz_booking_customers')->onDelete('set null');
            $table->enum('frequency', ['daily', 'weekly', 'biweekly', 'monthly']);
            $table->json('days_of_week')->nullable(); // For weekly: [1,3,5] = Mon, Wed, Fri
            $table->integer('day_of_month')->nullable(); // For monthly
            $table->time('preferred_time');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->integer('occurrences')->nullable(); // Alternative to end_date
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['user_id', 'is_active']);
        });

        // Appointment reminders
        Schema::create('growbiz_appointment_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained('growbiz_appointments')->onDelete('cascade');
            $table->enum('type', ['email', 'sms', 'whatsapp', 'push']);
            $table->enum('timing', ['1_hour', '2_hours', '24_hours', '48_hours', '1_week']);
            $table->timestamp('scheduled_at');
            $table->timestamp('sent_at')->nullable();
            $table->enum('status', ['pending', 'sent', 'failed', 'cancelled'])->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['scheduled_at', 'status']);
        });

        // Booking settings for the business
        Schema::create('growbiz_booking_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->string('business_name')->nullable();
            $table->text('booking_page_description')->nullable();
            $table->string('booking_page_slug')->nullable()->unique();
            $table->string('timezone')->default('Africa/Lusaka');
            $table->integer('slot_duration_minutes')->default(30); // Time slot intervals
            $table->integer('min_booking_notice_hours')->default(2); // Minimum advance booking
            $table->integer('max_booking_advance_days')->default(60); // How far ahead can book
            $table->boolean('require_approval')->default(false);
            $table->boolean('allow_cancellation')->default(true);
            $table->integer('cancellation_notice_hours')->default(24);
            $table->boolean('send_confirmation_email')->default(true);
            $table->boolean('send_reminder_sms')->default(false);
            $table->boolean('send_reminder_whatsapp')->default(false);
            $table->json('reminder_timings')->nullable();
            $table->boolean('collect_payment_online')->default(false);
            $table->decimal('deposit_percentage', 5, 2)->nullable();
            $table->string('cancellation_policy')->nullable();
            $table->json('custom_fields')->nullable(); // Additional booking form fields
            $table->string('logo')->nullable();
            $table->string('primary_color', 7)->default('#3b82f6');
            $table->timestamps();
        });

        // Public booking page access tokens
        Schema::create('growbiz_booking_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('token', 64)->unique();
            $table->string('name')->nullable(); // e.g., "Main Booking Page", "VIP Clients"
            $table->foreignId('service_id')->nullable()->constrained('growbiz_services')->onDelete('cascade');
            $table->foreignId('provider_id')->nullable()->constrained('growbiz_service_providers')->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->integer('usage_count')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['token', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('growbiz_booking_links');
        Schema::dropIfExists('growbiz_booking_settings');
        Schema::dropIfExists('growbiz_appointment_reminders');
        Schema::dropIfExists('growbiz_recurring_appointments');
        Schema::dropIfExists('growbiz_appointments');
        Schema::dropIfExists('growbiz_booking_customers');
        Schema::dropIfExists('growbiz_availability_exceptions');
        Schema::dropIfExists('growbiz_availability_schedules');
        Schema::dropIfExists('growbiz_service_provider_services');
        Schema::dropIfExists('growbiz_service_providers');
        Schema::dropIfExists('growbiz_services');
    }
};
