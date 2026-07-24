<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'growbiz_appointment_reminders',
        'growbiz_appointments',
        'growbiz_availability_exceptions',
        'growbiz_availability_schedules',
        'growbiz_booking_customers',
        'growbiz_booking_links',
        'growbiz_booking_settings',
        'growbiz_business_profiles',
        'growbiz_employee_invitations',
        'growbiz_employees',
        'growbiz_kanban_columns',
        'growbiz_milestones',
        'growbiz_pos_quick_products',
        'growbiz_pos_sale_items',
        'growbiz_pos_sales',
        'growbiz_pos_settings',
        'growbiz_pos_shifts',
        'growbiz_project_members',
        'growbiz_projects',
        'growbiz_recurring_appointments',
        'growbiz_service_provider_services',
        'growbiz_service_providers',
        'growbiz_services',
        'growbiz_task_assignments',
        'growbiz_task_attachments',
        'growbiz_task_categories',
        'growbiz_task_comments',
        'growbiz_task_dependencies',
        'growbiz_task_updates',
        'growbiz_tasks',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::dropIfExists($table);
            }
        }
    }

    public function down(): void
    {
        // Tables were created by specific migrations — no automatic restore
    }
};
