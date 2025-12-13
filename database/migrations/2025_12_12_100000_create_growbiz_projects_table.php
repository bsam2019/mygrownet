<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * GrowBiz Project Management Enhancement
     * 
     * Adds project containers for tasks with Kanban board support
     */
    public function up(): void
    {
        // Projects - containers for grouping related tasks
        Schema::create('growbiz_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manager_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('color', 7)->default('#3b82f6');
            $table->enum('status', ['planning', 'active', 'on_hold', 'completed', 'archived'])->default('planning');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('budget', 12, 2)->nullable();
            $table->string('currency', 3)->default('ZMW');
            $table->integer('progress_percentage')->default(0);
            $table->json('settings')->nullable(); // Kanban columns, view preferences
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['manager_id', 'status']);
            $table->index(['manager_id', 'sort_order']);
        });

        // Project members - who can access the project
        Schema::create('growbiz_project_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('growbiz_projects')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('growbiz_employees')->onDelete('cascade');
            $table->enum('role', ['viewer', 'member', 'admin'])->default('member');
            $table->timestamps();

            $table->unique(['project_id', 'employee_id']);
        });

        // Kanban columns - customizable columns per project
        Schema::create('growbiz_kanban_columns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('growbiz_projects')->onDelete('cascade');
            $table->string('name');
            $table->string('color', 7)->default('#6b7280');
            $table->integer('sort_order')->default(0);
            $table->integer('wip_limit')->nullable(); // Work in progress limit
            $table->boolean('is_done_column')->default(false); // Marks tasks as completed
            $table->timestamps();

            $table->index(['project_id', 'sort_order']);
        });

        // Milestones - key project checkpoints
        Schema::create('growbiz_milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('growbiz_projects')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->date('due_date')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
            $table->timestamp('completed_at')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['project_id', 'due_date']);
        });

        // Add project_id and kanban_column_id to tasks
        Schema::table('growbiz_tasks', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->after('manager_id')
                ->constrained('growbiz_projects')->onDelete('set null');
            $table->foreignId('kanban_column_id')->nullable()->after('project_id')
                ->constrained('growbiz_kanban_columns')->onDelete('set null');
            $table->foreignId('milestone_id')->nullable()->after('kanban_column_id')
                ->constrained('growbiz_milestones')->onDelete('set null');
            $table->foreignId('parent_task_id')->nullable()->after('milestone_id')
                ->constrained('growbiz_tasks')->onDelete('cascade');
            $table->integer('kanban_order')->default(0)->after('parent_task_id');
            
            $table->index(['project_id', 'kanban_column_id', 'kanban_order']);
            $table->index(['project_id', 'milestone_id']);
            $table->index('parent_task_id');
        });

        // Task dependencies - for Gantt chart
        Schema::create('growbiz_task_dependencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('growbiz_tasks')->onDelete('cascade');
            $table->foreignId('depends_on_task_id')->constrained('growbiz_tasks')->onDelete('cascade');
            $table->enum('type', ['finish_to_start', 'start_to_start', 'finish_to_finish', 'start_to_finish'])
                ->default('finish_to_start');
            $table->integer('lag_days')->default(0); // Days between tasks
            $table->timestamps();

            $table->unique(['task_id', 'depends_on_task_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('growbiz_task_dependencies');
        
        Schema::table('growbiz_tasks', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropForeign(['kanban_column_id']);
            $table->dropForeign(['milestone_id']);
            $table->dropForeign(['parent_task_id']);
            $table->dropColumn(['project_id', 'kanban_column_id', 'milestone_id', 'parent_task_id', 'kanban_order']);
        });

        Schema::dropIfExists('growbiz_milestones');
        Schema::dropIfExists('growbiz_kanban_columns');
        Schema::dropIfExists('growbiz_project_members');
        Schema::dropIfExists('growbiz_projects');
    }
};
