<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Vehicles
        Schema::create('cms_vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
            $table->string('vehicle_number')->unique();
            $table->string('registration_number')->unique();
            $table->string('make');
            $table->string('model');
            $table->integer('year')->nullable();
            $table->enum('vehicle_type', ['car', 'van', 'truck', 'pickup', 'motorcycle', 'other'])->default('van');
            $table->string('color')->nullable();
            $table->string('vin')->nullable(); // Vehicle Identification Number
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_price', 10, 2)->nullable();
            $table->decimal('current_value', 10, 2)->nullable();
            $table->integer('current_mileage')->default(0);
            $table->enum('fuel_type', ['petrol', 'diesel', 'electric', 'hybrid'])->default('petrol');
            $table->decimal('fuel_capacity', 10, 2)->nullable();
            $table->enum('status', ['active', 'maintenance', 'inactive', 'sold'])->default('active');
            $table->foreignId('assigned_driver_id')->nullable()->constrained('users');
            $table->date('insurance_expiry')->nullable();
            $table->string('insurance_company')->nullable();
            $table->string('insurance_policy_number')->nullable();
            $table->date('road_tax_expiry')->nullable();
            $table->date('fitness_certificate_expiry')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['company_id', 'status']);
            $table->index(['assigned_driver_id']);
        });

        // Fuel Records
        Schema::create('cms_fuel_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained('cms_vehicles')->cascadeOnDelete();
            $table->string('record_number')->unique();
            $table->date('fuel_date');
            $table->time('fuel_time')->nullable();
            $table->decimal('quantity', 10, 2); // Liters
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_cost', 10, 2);
            $table->integer('odometer_reading');
            $table->string('fuel_station')->nullable();
            $table->string('receipt_number')->nullable();
            $table->enum('fuel_type', ['petrol', 'diesel', 'electric'])->default('petrol');
            $table->boolean('is_full_tank')->default(false);
            $table->foreignId('filled_by')->constrained('users');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['company_id', 'fuel_date']);
            $table->index(['vehicle_id']);
        });

        // Vehicle Maintenance
        Schema::create('cms_vehicle_maintenance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained('cms_vehicles')->cascadeOnDelete();
            $table->string('maintenance_number')->unique();
            $table->date('maintenance_date');
            $table->enum('maintenance_type', ['routine', 'repair', 'inspection', 'emergency'])->default('routine');
            $table->enum('service_type', ['oil_change', 'tire_rotation', 'brake_service', 'engine_repair', 'body_work', 'electrical', 'other'])->nullable();
            $table->text('description');
            $table->integer('odometer_reading');
            $table->decimal('cost', 10, 2)->default(0);
            $table->string('service_provider')->nullable();
            $table->string('invoice_number')->nullable();
            $table->date('next_service_date')->nullable();
            $table->integer('next_service_mileage')->nullable();
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
            $table->foreignId('performed_by')->nullable()->constrained('users');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['company_id', 'maintenance_date']);
            $table->index(['vehicle_id']);
            $table->index(['status']);
        });

        // Trip Logs
        Schema::create('cms_trip_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained('cms_vehicles')->cascadeOnDelete();
            $table->string('trip_number')->unique();
            $table->date('trip_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->foreignId('driver_id')->constrained('users');
            $table->text('start_location');
            $table->text('end_location');
            $table->integer('start_odometer');
            $table->integer('end_odometer')->nullable();
            $table->decimal('distance', 10, 2)->nullable(); // Calculated
            $table->enum('trip_purpose', ['delivery', 'installation', 'site_visit', 'pickup', 'personal', 'other'])->default('delivery');
            $table->foreignId('job_id')->nullable()->constrained('cms_jobs');
            $table->foreignId('project_id')->nullable()->constrained('cms_projects');
            $table->text('passengers')->nullable();
            $table->text('cargo_description')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['company_id', 'trip_date']);
            $table->index(['vehicle_id']);
            $table->index(['driver_id']);
        });

        // Vehicle Expenses
        Schema::create('cms_vehicle_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained('cms_vehicles')->cascadeOnDelete();
            $table->string('expense_number')->unique();
            $table->date('expense_date');
            $table->enum('expense_type', ['fuel', 'maintenance', 'insurance', 'tax', 'parking', 'toll', 'fine', 'other'])->default('other');
            $table->text('description');
            $table->decimal('amount', 10, 2);
            $table->string('vendor')->nullable();
            $table->string('receipt_number')->nullable();
            $table->foreignId('paid_by')->constrained('users');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['company_id', 'expense_date']);
            $table->index(['vehicle_id']);
            $table->index(['expense_type']);
        });

        // Vehicle Documents
        Schema::create('cms_vehicle_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('cms_vehicles')->cascadeOnDelete();
            $table->enum('document_type', ['registration', 'insurance', 'fitness', 'permit', 'other'])->default('other');
            $table->string('document_name');
            $table->string('document_number')->nullable();
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['vehicle_id', 'document_type']);
            $table->index(['expiry_date']);
        });

        // Driver Assignments
        Schema::create('cms_driver_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained('cms_vehicles')->cascadeOnDelete();
            $table->foreignId('driver_id')->constrained('users');
            $table->date('assigned_date');
            $table->date('return_date')->nullable();
            $table->enum('assignment_type', ['permanent', 'temporary'])->default('temporary');
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['company_id', 'status']);
            $table->index(['vehicle_id']);
            $table->index(['driver_id']);
        });

        // Vehicle Inspections
        Schema::create('cms_vehicle_inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('cms_vehicles')->cascadeOnDelete();
            $table->date('inspection_date');
            $table->enum('inspection_type', ['daily', 'weekly', 'monthly', 'pre_trip', 'post_trip'])->default('daily');
            $table->foreignId('inspector_id')->constrained('users');
            $table->integer('odometer_reading');
            $table->json('checklist_items')->nullable(); // Array of items checked
            $table->enum('overall_condition', ['excellent', 'good', 'fair', 'poor'])->default('good');
            $table->text('issues_found')->nullable();
            $table->text('recommendations')->nullable();
            $table->boolean('requires_maintenance')->default(false);
            $table->timestamps();
            
            $table->index(['vehicle_id', 'inspection_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_vehicle_inspections');
        Schema::dropIfExists('cms_driver_assignments');
        Schema::dropIfExists('cms_vehicle_documents');
        Schema::dropIfExists('cms_vehicle_expenses');
        Schema::dropIfExists('cms_trip_logs');
        Schema::dropIfExists('cms_vehicle_maintenance');
        Schema::dropIfExists('cms_fuel_records');
        Schema::dropIfExists('cms_vehicles');
    }
};
