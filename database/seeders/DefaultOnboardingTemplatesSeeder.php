<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Infrastructure\Persistence\Eloquent\CMS\OnboardingTemplateModel;
use App\Infrastructure\Persistence\Eloquent\CMS\OnboardingTaskModel;
use App\Infrastructure\Persistence\Eloquent\CMS\CompanyModel;

class DefaultOnboardingTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        $companies = CompanyModel::all();

        foreach ($companies as $company) {
            // Check if template already exists
            if (OnboardingTemplateModel::where('company_id', $company->id)->exists()) {
                $this->command->warn("Onboarding templates already exist for company: {$company->company_name}");
                continue;
            }

            // Create default template
            $template = OnboardingTemplateModel::create([
                'company_id' => $company->id,
                'template_name' => 'Standard Employee Onboarding',
                'is_default' => true,
            ]);

            // Day 1 Tasks
            OnboardingTaskModel::create([
                'template_id' => $template->id,
                'task_name' => 'Complete employment contract',
                'description' => 'Review and sign employment contract',
                'task_category' => 'documentation',
                'assigned_to_role' => 'employee',
                'due_days_after_start' => 0,
                'is_mandatory' => true,
                'display_order' => 1,
            ]);

            OnboardingTaskModel::create([
                'template_id' => $template->id,
                'task_name' => 'Submit identification documents',
                'description' => 'Provide NRC, passport, and other required documents',
                'task_category' => 'documentation',
                'assigned_to_role' => 'employee',
                'due_days_after_start' => 0,
                'is_mandatory' => true,
                'display_order' => 2,
            ]);

            OnboardingTaskModel::create([
                'template_id' => $template->id,
                'task_name' => 'Create system accounts',
                'description' => 'Set up email, CMS access, and other system accounts',
                'task_category' => 'system_access',
                'assigned_to_role' => 'it',
                'due_days_after_start' => 0,
                'is_mandatory' => true,
                'display_order' => 3,
            ]);

            OnboardingTaskModel::create([
                'template_id' => $template->id,
                'task_name' => 'Provide office equipment',
                'description' => 'Issue laptop, phone, and other necessary equipment',
                'task_category' => 'equipment',
                'assigned_to_role' => 'it',
                'due_days_after_start' => 0,
                'is_mandatory' => true,
                'display_order' => 4,
            ]);

            // Week 1 Tasks
            OnboardingTaskModel::create([
                'template_id' => $template->id,
                'task_name' => 'Company orientation',
                'description' => 'Introduction to company culture, values, and policies',
                'task_category' => 'training',
                'assigned_to_role' => 'hr',
                'due_days_after_start' => 3,
                'is_mandatory' => true,
                'display_order' => 5,
            ]);

            OnboardingTaskModel::create([
                'template_id' => $template->id,
                'task_name' => 'Meet team members',
                'description' => 'Introduction to team and key stakeholders',
                'task_category' => 'introduction',
                'assigned_to_role' => 'manager',
                'due_days_after_start' => 3,
                'is_mandatory' => true,
                'display_order' => 6,
            ]);

            OnboardingTaskModel::create([
                'template_id' => $template->id,
                'task_name' => 'Department training',
                'description' => 'Role-specific training and procedures',
                'task_category' => 'training',
                'assigned_to_role' => 'manager',
                'due_days_after_start' => 5,
                'is_mandatory' => true,
                'display_order' => 7,
            ]);

            // Week 2 Tasks
            OnboardingTaskModel::create([
                'template_id' => $template->id,
                'task_name' => 'Complete health and safety training',
                'description' => 'Workplace safety procedures and emergency protocols',
                'task_category' => 'training',
                'assigned_to_role' => 'hr',
                'due_days_after_start' => 10,
                'is_mandatory' => true,
                'display_order' => 8,
            ]);

            OnboardingTaskModel::create([
                'template_id' => $template->id,
                'task_name' => 'Set up bank account for payroll',
                'description' => 'Provide bank details for salary payments',
                'task_category' => 'documentation',
                'assigned_to_role' => 'employee',
                'due_days_after_start' => 10,
                'is_mandatory' => true,
                'display_order' => 9,
            ]);

            // Month 1 Tasks
            OnboardingTaskModel::create([
                'template_id' => $template->id,
                'task_name' => 'First month review',
                'description' => 'Check-in meeting with manager to discuss progress',
                'task_category' => 'other',
                'assigned_to_role' => 'manager',
                'due_days_after_start' => 30,
                'is_mandatory' => true,
                'display_order' => 10,
            ]);

            $this->command->info("Created default onboarding template for: {$company->company_name}");
        }
    }
}
