<?php

namespace App\Domain\BMS\Operations\Services;

use App\Infrastructure\Persistence\Eloquent\BMS\TaskModel;
use App\Infrastructure\Persistence\Eloquent\BMS\TaskTriggerModel;
use App\Infrastructure\Persistence\Eloquent\BMS\TaskTriggerExecutionModel;
use App\Infrastructure\Persistence\Eloquent\BMS\LeadModel;
use App\Infrastructure\Persistence\Eloquent\BMS\InvoiceModel;
use Illuminate\Support\Facades\DB;

class IntegrationService
{
    /**
     * Create task from CRM lead
     */
    public function createTaskFromLead(int $leadId): array
    {
        try {
            $lead = LeadModel::findOrFail($leadId);

            $task = TaskModel::create([
                'company_id' => $lead->company_id,
                'title' => "Follow up: {$lead->name}",
                'description' => "Follow up with lead: {$lead->name}\nEmail: {$lead->email}\nPhone: {$lead->phone}\nSource: {$lead->source}",
                'type' => 'follow_up',
                'status' => 'pending',
                'priority' => $this->determinePriorityFromLead($lead),
                'workflow_id' => $this->getDefaultWorkflowId($lead->company_id),
                'created_by' => auth()->id() ?? 1,
                'due_date' => now()->addDays(2), // Follow up in 2 days
            ]);

            // Log trigger execution
            $this->logTriggerExecution('crm_lead', $leadId, $task->id, 'success');

            return [
                'success' => true,
                'task_id' => $task->id,
                'message' => 'Task created from lead successfully',
            ];
        } catch (\Exception $e) {
            $this->logTriggerExecution('crm_lead', $leadId, null, 'failed', $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Create invoice when task is completed
     */
    public function createInvoiceFromTask(int $taskId): array
    {
        try {
            $task = TaskModel::with('job')->findOrFail($taskId);

            if (!$task->job_id) {
                throw new \Exception('Task is not linked to a job');
            }

            // Check if invoice already exists
            $existingInvoice = InvoiceModel::where('job_id', $task->job_id)
                ->where('notes', 'like', "%Task #{$task->task_number}%")
                ->first();

            if ($existingInvoice) {
                return [
                    'success' => false,
                    'error' => 'Invoice already exists for this task',
                ];
            }

            $invoice = InvoiceModel::create([
                'company_id' => $task->company_id,
                'customer_id' => $task->job->customer_id,
                'job_id' => $task->job_id,
                'invoice_number' => $this->generateInvoiceNumber($task->company_id),
                'invoice_date' => now(),
                'due_date' => now()->addDays(30),
                'subtotal' => $task->actual_cost ?? $task->estimated_cost ?? 0,
                'tax_amount' => 0,
                'total_amount' => $task->actual_cost ?? $task->estimated_cost ?? 0,
                'status' => 'draft',
                'notes' => "Auto-generated from completed task: {$task->title} (Task #{$task->task_number})",
            ]);

            // Add invoice item
            $invoice->items()->create([
                'description' => $task->title,
                'quantity' => 1,
                'unit_price' => $task->actual_cost ?? $task->estimated_cost ?? 0,
                'total' => $task->actual_cost ?? $task->estimated_cost ?? 0,
            ]);

            $this->logTriggerExecution('task_completed', $taskId, $task->id, 'success', [
                'invoice_id' => $invoice->id,
            ]);

            return [
                'success' => true,
                'invoice_id' => $invoice->id,
                'message' => 'Invoice created successfully',
            ];
        } catch (\Exception $e) {
            $this->logTriggerExecution('task_completed', $taskId, $taskId, 'failed', $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check employee availability from HR
     */
    public function checkEmployeeAvailability(int $companyId, int $userId, string $date): array
    {
        // Check if employee has leave on this date
        $leave = DB::table('cms_leave_requests')
            ->where('company_id', $companyId)
            ->where('worker_id', $userId)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->first();

        if ($leave) {
            return [
                'available' => false,
                'reason' => 'On leave',
                'leave_type' => $leave->leave_type ?? 'Unknown',
            ];
        }

        // Check attendance
        $attendance = DB::table('cms_attendance_records')
            ->where('company_id', $companyId)
            ->where('worker_id', $userId)
            ->whereDate('date', $date)
            ->first();

        if ($attendance && $attendance->status === 'absent') {
            return [
                'available' => false,
                'reason' => 'Marked absent',
            ];
        }

        // Check shift schedule
        $shift = DB::table('cms_shifts')
            ->where('company_id', $companyId)
            ->where('worker_id', $userId)
            ->whereDate('date', $date)
            ->first();

        return [
            'available' => true,
            'shift' => $shift ? [
                'start_time' => $shift->start_time,
                'end_time' => $shift->end_time,
            ] : null,
        ];
    }

    /**
     * Get available employees for task assignment
     */
    public function getAvailableEmployees(int $companyId, string $date, ?string $skillRequired = null): array
    {
        $workers = DB::table('cms_workers')
            ->where('company_id', $companyId)
            ->where('status', 'active')
            ->get();

        $available = [];

        foreach ($workers as $worker) {
            $availability = $this->checkEmployeeAvailability($companyId, $worker->id, $date);
            
            if ($availability['available']) {
                // Check current workload
                $taskCount = TaskModel::where('company_id', $companyId)
                    ->where('assigned_to', $worker->user_id)
                    ->whereIn('status', ['pending', 'in_progress'])
                    ->count();

                $available[] = [
                    'worker_id' => $worker->id,
                    'user_id' => $worker->user_id,
                    'name' => $worker->first_name . ' ' . $worker->last_name,
                    'current_tasks' => $taskCount,
                    'shift' => $availability['shift'],
                ];
            }
        }

        // Sort by current workload (least busy first)
        usort($available, fn($a, $b) => $a['current_tasks'] <=> $b['current_tasks']);

        return $available;
    }

    /**
     * Setup automatic trigger
     */
    public function setupTrigger(int $companyId, array $data): array
    {
        $trigger = TaskTriggerModel::create([
            'company_id' => $companyId,
            'trigger_type' => $data['trigger_type'],
            'action_type' => $data['action_type'],
            'trigger_conditions' => $data['conditions'] ?? [],
            'action_config' => $data['config'] ?? [],
            'is_active' => $data['is_active'] ?? true,
        ]);

        return [
            'success' => true,
            'trigger_id' => $trigger->id,
            'message' => 'Trigger created successfully',
        ];
    }

    /**
     * Execute trigger
     */
    public function executeTrigger(int $triggerId, array $data): array
    {
        $trigger = TaskTriggerModel::findOrFail($triggerId);

        if (!$trigger->is_active) {
            return [
                'success' => false,
                'error' => 'Trigger is not active',
            ];
        }

        // Check conditions
        if (!$this->checkTriggerConditions($trigger->trigger_conditions, $data)) {
            $this->logTriggerExecution($trigger->trigger_type, null, null, 'skipped', 'Conditions not met');
            
            return [
                'success' => false,
                'error' => 'Trigger conditions not met',
            ];
        }

        // Execute action
        $result = match ($trigger->action_type) {
            'create_task' => $this->executeCreateTaskAction($trigger, $data),
            'create_invoice' => $this->executeCreateInvoiceAction($trigger, $data),
            'send_notification' => $this->executeSendNotificationAction($trigger, $data),
            default => ['success' => false, 'error' => 'Unknown action type'],
        };

        return $result;
    }

    private function checkTriggerConditions(array $conditions, array $data): bool
    {
        foreach ($conditions as $key => $value) {
            if (!isset($data[$key]) || $data[$key] !== $value) {
                return false;
            }
        }
        return true;
    }

    private function executeCreateTaskAction(TaskTriggerModel $trigger, array $data): array
    {
        $config = $trigger->action_config;
        
        $task = TaskModel::create([
            'company_id' => $trigger->company_id,
            'title' => $config['title'] ?? 'Auto-generated task',
            'description' => $config['description'] ?? '',
            'type' => $config['type'] ?? 'task',
            'status' => 'pending',
            'priority' => $config['priority'] ?? 'medium',
            'workflow_id' => $config['workflow_id'] ?? $this->getDefaultWorkflowId($trigger->company_id),
            'assigned_to' => $config['assigned_to'] ?? null,
            'created_by' => 1,
        ]);

        $this->logTriggerExecution($trigger->trigger_type, $trigger->id, $task->id, 'success');

        return [
            'success' => true,
            'task_id' => $task->id,
        ];
    }

    private function executeCreateInvoiceAction(TaskTriggerModel $trigger, array $data): array
    {
        if (!isset($data['task_id'])) {
            return ['success' => false, 'error' => 'Task ID required'];
        }

        return $this->createInvoiceFromTask($data['task_id']);
    }

    private function executeSendNotificationAction(TaskTriggerModel $trigger, array $data): array
    {
        // Implement notification sending logic
        return ['success' => true, 'message' => 'Notification sent'];
    }

    private function logTriggerExecution(string $triggerType, ?int $triggerId, ?int $taskId, string $status, $errorOrResult = null): void
    {
        if ($triggerId) {
            TaskTriggerExecutionModel::create([
                'trigger_id' => $triggerId,
                'task_id' => $taskId,
                'trigger_data' => request()->all(),
                'action_result' => is_array($errorOrResult) ? $errorOrResult : [],
                'status' => $status,
                'error_message' => is_string($errorOrResult) ? $errorOrResult : null,
                'executed_at' => now(),
            ]);
        }
    }

    private function determinePriorityFromLead($lead): string
    {
        // Implement logic to determine priority based on lead data
        return 'medium';
    }

    private function getDefaultWorkflowId(int $companyId): int
    {
        $workflow = DB::table('cms_workflows')
            ->where('company_id', $companyId)
            ->where('is_default', true)
            ->first();

        return $workflow?->id ?? 1;
    }

    private function generateInvoiceNumber(int $companyId): string
    {
        $lastInvoice = InvoiceModel::where('company_id', $companyId)
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = $lastInvoice ? ((int) substr($lastInvoice->invoice_number, -4)) + 1 : 1;

        return 'INV-' . date('Ymd') . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
