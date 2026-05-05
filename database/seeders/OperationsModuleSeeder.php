<?php

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\CMS\WorkflowModel;
use App\Infrastructure\Persistence\Eloquent\CMS\WorkflowStageModel;
use Illuminate\Database\Seeder;

class OperationsModuleSeeder extends Seeder
{
    public function run(): void
    {
        // This seeder creates default workflows for companies
        // It should be run when a company enables the operations module
        
        $this->createDefaultWorkflows();
    }

    private function createDefaultWorkflows(): void
    {
        $workflows = [
            [
                'name' => 'General Task Workflow',
                'description' => 'Standard workflow for general tasks',
                'workflow_type' => 'general',
                'is_default' => true,
                'stages' => [
                    ['name' => 'To Do', 'color' => '#6b7280', 'requires_approval' => false],
                    ['name' => 'In Progress', 'color' => '#3b82f6', 'requires_approval' => false],
                    ['name' => 'Review', 'color' => '#f59e0b', 'requires_approval' => true],
                    ['name' => 'Done', 'color' => '#10b981', 'requires_approval' => false],
                ],
            ],
            [
                'name' => 'Production Workflow',
                'description' => 'Workflow for production/manufacturing tasks',
                'workflow_type' => 'production',
                'is_default' => false,
                'stages' => [
                    ['name' => 'Pending', 'color' => '#6b7280', 'requires_approval' => false],
                    ['name' => 'Cutting', 'color' => '#3b82f6', 'requires_approval' => false],
                    ['name' => 'Assembly', 'color' => '#8b5cf6', 'requires_approval' => false],
                    ['name' => 'Finishing', 'color' => '#ec4899', 'requires_approval' => false],
                    ['name' => 'Quality Check', 'color' => '#f59e0b', 'requires_approval' => true],
                    ['name' => 'Packaging', 'color' => '#14b8a6', 'requires_approval' => false],
                    ['name' => 'Completed', 'color' => '#10b981', 'requires_approval' => false],
                ],
            ],
            [
                'name' => 'Installation Workflow',
                'description' => 'Workflow for installation tasks',
                'workflow_type' => 'installation',
                'is_default' => false,
                'stages' => [
                    ['name' => 'Scheduled', 'color' => '#6b7280', 'requires_approval' => false],
                    ['name' => 'Pre-Installation Check', 'color' => '#3b82f6', 'requires_approval' => false],
                    ['name' => 'In Progress', 'color' => '#8b5cf6', 'requires_approval' => false],
                    ['name' => 'Quality Inspection', 'color' => '#f59e0b', 'requires_approval' => true],
                    ['name' => 'Customer Sign-off', 'color' => '#14b8a6', 'requires_approval' => true],
                    ['name' => 'Completed', 'color' => '#10b981', 'requires_approval' => false],
                ],
            ],
            [
                'name' => 'Maintenance Workflow',
                'description' => 'Workflow for maintenance tasks',
                'workflow_type' => 'maintenance',
                'is_default' => false,
                'stages' => [
                    ['name' => 'Reported', 'color' => '#6b7280', 'requires_approval' => false],
                    ['name' => 'Assigned', 'color' => '#3b82f6', 'requires_approval' => false],
                    ['name' => 'In Progress', 'color' => '#8b5cf6', 'requires_approval' => false],
                    ['name' => 'Testing', 'color' => '#f59e0b', 'requires_approval' => false],
                    ['name' => 'Completed', 'color' => '#10b981', 'requires_approval' => false],
                ],
            ],
        ];

        // Note: This creates templates. Actual company-specific workflows
        // should be created when a company enables the operations module
        foreach ($workflows as $workflowData) {
            $stages = $workflowData['stages'];
            unset($workflowData['stages']);
            
            // This is a template - company_id will be set when copied to a company
            $workflowData['company_id'] = 1; // Placeholder
            
            $workflow = WorkflowModel::create($workflowData);
            
            foreach ($stages as $index => $stageData) {
                $stageData['workflow_id'] = $workflow->id;
                $stageData['sequence_order'] = $index + 1;
                WorkflowStageModel::create($stageData);
            }
        }
    }
}
