<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Departments
        if (!Schema::hasTable('departments')) {
            Schema::create('departments', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code')->unique()->nullable();
                $table->text('description')->nullable();
                $table->foreignId('parent_id')->nullable()->constrained('departments')->onDelete('set null');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // Positions
        if (!Schema::hasTable('positions')) {
            Schema::create('positions', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('code')->unique()->nullable();
                $table->text('description')->nullable();
                $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('set null');
                $table->integer('level')->default(1);
                $table->decimal('min_salary', 12, 2)->nullable();
                $table->decimal('max_salary', 12, 2)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // Employees
        if (!Schema::hasTable('employees')) {
            Schema::create('employees', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->string('employee_number')->unique();
                $table->string('first_name');
                $table->string('last_name');
                $table->string('email')->unique();
                $table->string('phone')->nullable();
                $table->text('address')->nullable();
                $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('set null');
                $table->foreignId('position_id')->nullable()->constrained('positions')->onDelete('set null');
                $table->foreignId('manager_id')->nullable()->constrained('employees')->onDelete('set null');
                $table->date('hire_date');
                $table->enum('employment_status', ['active', 'inactive', 'terminated', 'on_leave'])->default('active');
                $table->enum('employment_type', ['full_time', 'part_time', 'contract', 'intern'])->default('full_time');
                $table->decimal('salary', 12, 2)->nullable();
                $table->string('emergency_contact_name')->nullable();
                $table->string('emergency_contact_phone')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['department_id', 'employment_status']);
                $table->index('manager_id');
            });

            // Add manager_id to departments after employees table exists
            Schema::table('departments', function (Blueprint $table) {
                if (!Schema::hasColumn('departments', 'manager_id')) {
                    $table->foreignId('manager_id')->nullable()->after('description')->constrained('employees')->onDelete('set null');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            if (Schema::hasColumn('departments', 'manager_id')) {
                $table->dropForeign(['manager_id']);
                $table->dropColumn('manager_id');
            }
        });

        Schema::dropIfExists('employees');
        Schema::dropIfExists('positions');
        Schema::dropIfExists('departments');
    }
};
