<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Infrastructure\Persistence\Eloquent\DepartmentModel;
use App\Infrastructure\Persistence\Eloquent\PositionModel;

class OrganizationalStructureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Seeds the organizational structure based on docs/ORGANIZATIONAL_STRUCTURE.md
     * Phase 1: SHORT TERM (0-6 Months) positions
     */
    public function run(): void
    {
        // Create core departments if they don't exist
        $departments = $this->createDepartments();
        
        // Create C-Level positions
        $this->createCLevelPositions($departments);
        
        // Create Phase 1 positions (SHORT TERM - 0-6 months)
        $this->createPhase1Positions($departments);
        
        // Create KPIs for key positions
        $this->createPositionKPIs();
        
        // Create responsibilities for key positions
        $this->createPositionResponsibilities();
        
        // Create hiring roadmap
        $this->createHiringRoadmap();
    }

    private function createDepartments(): array
    {
        $departments = [];
        
        // Executive
        $departments['executive'] = DepartmentModel::firstOrCreate(
            ['name' => 'Executive'],
            [
                'description' => 'Executive leadership team',
                'is_active' => true
            ]
        );
        
        // Operations
        $departments['operations'] = DepartmentModel::firstOrCreate(
            ['name' => 'Operations'],
            [
                'description' => 'Operations and member services',
                'is_active' => true
            ]
        );
        
        // Finance
        $departments['finance'] = DepartmentModel::firstOrCreate(
            ['name' => 'Finance & Compliance'],
            [
                'description' => 'Financial management and regulatory compliance',
                'is_active' => true
            ]
        );
        
        // Technology
        $departments['technology'] = DepartmentModel::firstOrCreate(
            ['name' => 'Technology'],
            [
                'description' => 'Platform development and infrastructure',
                'is_active' => true
            ]
        );
        
        // Growth & Marketing
        $departments['growth'] = DepartmentModel::firstOrCreate(
            ['name' => 'Growth & Marketing'],
            [
                'description' => 'Member acquisition and brand building',
                'is_active' => true
            ]
        );
        
        return $departments;
    }

    private function createCLevelPositions(array $departments): void
    {
        // CEO/Managing Director
        $ceo = PositionModel::firstOrCreate(
            ['title' => 'Chief Executive Officer (CEO)'],
            [
                'department_id' => $departments['executive']->id,
                'description' => 'Overall strategic direction and leadership of MyGrowNet',
                'organizational_level' => 'c_level',
                'level' => 1,
                'min_salary' => 50000,
                'max_salary' => 100000,
                'is_active' => true
            ]
        );
        
        // COO
        PositionModel::firstOrCreate(
            ['title' => 'Chief Operating Officer (COO)'],
            [
                'department_id' => $departments['operations']->id,
                'description' => 'Overall operational strategy and execution',
                'organizational_level' => 'c_level',
                'reports_to_position_id' => $ceo->id,
                'level' => 2,
                'min_salary' => 40000,
                'max_salary' => 60000,
                'is_active' => true
            ]
        );
        
        // CFO
        PositionModel::firstOrCreate(
            ['title' => 'Chief Financial Officer (CFO)'],
            [
                'department_id' => $departments['finance']->id,
                'description' => 'Financial strategy, planning, and compliance',
                'organizational_level' => 'c_level',
                'reports_to_position_id' => $ceo->id,
                'level' => 2,
                'min_salary' => 45000,
                'max_salary' => 70000,
                'is_active' => true
            ]
        );
        
        // CTO
        PositionModel::firstOrCreate(
            ['title' => 'Chief Technology Officer (CTO)'],
            [
                'department_id' => $departments['technology']->id,
                'description' => 'Technology strategy, platform architecture, and innovation',
                'organizational_level' => 'c_level',
                'reports_to_position_id' => $ceo->id,
                'level' => 2,
                'min_salary' => 35000,
                'max_salary' => 55000,
                'is_active' => true
            ]
        );
        
        // CGO
        PositionModel::firstOrCreate(
            ['title' => 'Chief Growth Officer (CGO)'],
            [
                'department_id' => $departments['growth']->id,
                'description' => 'Growth strategy, marketing, and member acquisition',
                'organizational_level' => 'c_level',
                'reports_to_position_id' => $ceo->id,
                'level' => 2,
                'min_salary' => 35000,
                'max_salary' => 55000,
                'is_active' => true
            ]
        );
    }

    private function createPhase1Positions(array $departments): void
    {
        $coo = PositionModel::where('title', 'Chief Operating Officer (COO)')->first();
        $cfo = PositionModel::where('title', 'Chief Financial Officer (CFO)')->first();
        $cto = PositionModel::where('title', 'Chief Technology Officer (CTO)')->first();
        $cgo = PositionModel::where('title', 'Chief Growth Officer (CGO)')->first();
        
        // Operations Manager (PRIORITY #1)
        $opsManager = PositionModel::firstOrCreate(
            ['title' => 'Operations Manager'],
            [
                'department_id' => $departments['operations']->id,
                'description' => 'Oversee daily platform operations and member services',
                'organizational_level' => 'manager',
                'reports_to_position_id' => $coo?->id,
                'level' => 3,
                'min_salary' => 15000,
                'max_salary' => 25000,
                'is_active' => true
            ]
        );
        
        // Finance & Compliance Lead
        PositionModel::firstOrCreate(
            ['title' => 'Finance & Compliance Lead'],
            [
                'department_id' => $departments['finance']->id,
                'description' => 'Financial reporting, controls, and regulatory compliance',
                'organizational_level' => 'manager',
                'reports_to_position_id' => $cfo?->id,
                'level' => 3,
                'min_salary' => 12000,
                'max_salary' => 20000,
                'is_active' => true
            ]
        );
        
        // Technology Lead
        PositionModel::firstOrCreate(
            ['title' => 'Technology Lead'],
            [
                'department_id' => $departments['technology']->id,
                'description' => 'Platform maintenance, bug fixes, and infrastructure',
                'organizational_level' => 'manager',
                'reports_to_position_id' => $cto?->id,
                'level' => 3,
                'min_salary' => 10000,
                'max_salary' => 18000,
                'is_active' => true
            ]
        );
        
        // Growth & Marketing Lead
        PositionModel::firstOrCreate(
            ['title' => 'Growth & Marketing Lead'],
            [
                'department_id' => $departments['growth']->id,
                'description' => 'Member acquisition strategy and marketing campaigns',
                'organizational_level' => 'manager',
                'reports_to_position_id' => $cgo?->id,
                'level' => 3,
                'min_salary' => 10000,
                'max_salary' => 18000,
                'is_active' => true
            ]
        );
        
        // Member Support Team Lead
        PositionModel::firstOrCreate(
            ['title' => 'Member Support Team Lead'],
            [
                'department_id' => $departments['operations']->id,
                'description' => 'Lead member support team and ensure service quality',
                'organizational_level' => 'team_lead',
                'reports_to_position_id' => $opsManager->id,
                'level' => 4,
                'min_salary' => 6000,
                'max_salary' => 10000,
                'is_active' => true
            ]
        );
        
        // Member Support Agent
        PositionModel::firstOrCreate(
            ['title' => 'Member Support Agent'],
            [
                'department_id' => $departments['operations']->id,
                'description' => 'Provide member support and resolve issues',
                'organizational_level' => 'individual',
                'level' => 5,
                'min_salary' => 3000,
                'max_salary' => 5000,
                'is_active' => true
            ]
        );
        
        // Accountant
        PositionModel::firstOrCreate(
            ['title' => 'Accountant'],
            [
                'department_id' => $departments['finance']->id,
                'description' => 'Bookkeeping, reconciliation, and financial reporting',
                'organizational_level' => 'individual',
                'level' => 4,
                'min_salary' => 5000,
                'max_salary' => 8000,
                'is_active' => true
            ]
        );
        
        // Content Creator
        PositionModel::firstOrCreate(
            ['title' => 'Content Creator'],
            [
                'department_id' => $departments['growth']->id,
                'description' => 'Create engaging content for marketing campaigns',
                'organizational_level' => 'individual',
                'level' => 4,
                'min_salary' => 4000,
                'max_salary' => 7000,
                'is_active' => true
            ]
        );
        
        // Social Media Manager
        PositionModel::firstOrCreate(
            ['title' => 'Social Media Manager'],
            [
                'department_id' => $departments['growth']->id,
                'description' => 'Manage social media presence and engagement',
                'organizational_level' => 'individual',
                'level' => 4,
                'min_salary' => 4000,
                'max_salary' => 7000,
                'is_active' => true
            ]
        );
    }

    private function createPositionKPIs(): void
    {
        // Get positions
        $opsManager = PositionModel::where('title', 'Operations Manager')->first();
        $financeManager = PositionModel::where('title', 'Finance & Compliance Lead')->first();
        $techLead = PositionModel::where('title', 'Technology Lead')->first();
        $growthLead = PositionModel::where('title', 'Growth & Marketing Lead')->first();
        
        if ($opsManager) {
            DB::table('position_kpis')->insert([
                [
                    'position_id' => $opsManager->id,
                    'kpi_name' => 'Member Satisfaction Score',
                    'kpi_description' => 'Average member satisfaction rating',
                    'target_value' => 85.00,
                    'measurement_unit' => 'percentage',
                    'measurement_frequency' => 'monthly',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'position_id' => $opsManager->id,
                    'kpi_name' => 'Average Response Time',
                    'kpi_description' => 'Average time to respond to member inquiries',
                    'target_value' => 2.00,
                    'measurement_unit' => 'hours',
                    'measurement_frequency' => 'weekly',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'position_id' => $opsManager->id,
                    'kpi_name' => 'Payment Processing Time',
                    'kpi_description' => 'Average time to process payments',
                    'target_value' => 24.00,
                    'measurement_unit' => 'hours',
                    'measurement_frequency' => 'weekly',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);
        }
        
        if ($financeManager) {
            DB::table('position_kpis')->insert([
                [
                    'position_id' => $financeManager->id,
                    'kpi_name' => 'Financial Reporting Accuracy',
                    'kpi_description' => 'Accuracy of financial reports',
                    'target_value' => 100.00,
                    'measurement_unit' => 'percentage',
                    'measurement_frequency' => 'monthly',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'position_id' => $financeManager->id,
                    'kpi_name' => 'Commission Processing Accuracy',
                    'kpi_description' => 'Accuracy of commission calculations',
                    'target_value' => 99.50,
                    'measurement_unit' => 'percentage',
                    'measurement_frequency' => 'monthly',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'position_id' => $financeManager->id,
                    'kpi_name' => 'Compliance Incidents',
                    'kpi_description' => 'Number of compliance incidents',
                    'target_value' => 0.00,
                    'measurement_unit' => 'count',
                    'measurement_frequency' => 'monthly',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);
        }
        
        if ($techLead) {
            DB::table('position_kpis')->insert([
                [
                    'position_id' => $techLead->id,
                    'kpi_name' => 'Platform Uptime',
                    'kpi_description' => 'Platform availability percentage',
                    'target_value' => 99.50,
                    'measurement_unit' => 'percentage',
                    'measurement_frequency' => 'monthly',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'position_id' => $techLead->id,
                    'kpi_name' => 'Bug Resolution Time',
                    'kpi_description' => 'Average time to resolve critical bugs',
                    'target_value' => 48.00,
                    'measurement_unit' => 'hours',
                    'measurement_frequency' => 'weekly',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'position_id' => $techLead->id,
                    'kpi_name' => 'Security Incidents',
                    'kpi_description' => 'Number of security incidents',
                    'target_value' => 0.00,
                    'measurement_unit' => 'count',
                    'measurement_frequency' => 'monthly',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);
        }
        
        if ($growthLead) {
            DB::table('position_kpis')->insert([
                [
                    'position_id' => $growthLead->id,
                    'kpi_name' => 'New Member Registrations',
                    'kpi_description' => 'Number of new member registrations',
                    'target_value' => 100.00,
                    'measurement_unit' => 'count',
                    'measurement_frequency' => 'monthly',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'position_id' => $growthLead->id,
                    'kpi_name' => 'Marketing ROI',
                    'kpi_description' => 'Return on marketing investment',
                    'target_value' => 3.00,
                    'measurement_unit' => 'ratio',
                    'measurement_frequency' => 'monthly',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'position_id' => $growthLead->id,
                    'kpi_name' => 'Social Media Engagement Rate',
                    'kpi_description' => 'Social media engagement percentage',
                    'target_value' => 5.00,
                    'measurement_unit' => 'percentage',
                    'measurement_frequency' => 'weekly',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);
        }
    }

    private function createPositionResponsibilities(): void
    {
        $opsManager = PositionModel::where('title', 'Operations Manager')->first();
        
        if ($opsManager) {
            DB::table('position_responsibilities')->insert([
                [
                    'position_id' => $opsManager->id,
                    'responsibility_title' => 'Oversee daily platform operations',
                    'responsibility_description' => 'Manage all daily operational activities and ensure smooth platform functioning',
                    'priority' => 'critical',
                    'category' => 'operational',
                    'display_order' => 1,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'position_id' => $opsManager->id,
                    'responsibility_title' => 'Manage member support and onboarding',
                    'responsibility_description' => 'Lead member support team and ensure quality service delivery',
                    'priority' => 'critical',
                    'category' => 'operational',
                    'display_order' => 2,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'position_id' => $opsManager->id,
                    'responsibility_title' => 'Handle payment approvals and KYC verification',
                    'responsibility_description' => 'Process and approve payments, verify member identities',
                    'priority' => 'high',
                    'category' => 'operational',
                    'display_order' => 3,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'position_id' => $opsManager->id,
                    'responsibility_title' => 'Coordinate between departments',
                    'responsibility_description' => 'Ensure effective communication and collaboration across teams',
                    'priority' => 'high',
                    'category' => 'administrative',
                    'display_order' => 4,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'position_id' => $opsManager->id,
                    'responsibility_title' => 'Implement operational processes and SOPs',
                    'responsibility_description' => 'Develop and document standard operating procedures',
                    'priority' => 'medium',
                    'category' => 'strategic',
                    'display_order' => 5,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);
        }
    }

    private function createHiringRoadmap(): void
    {
        // Phase 1 (Month 1) - Critical hires
        $opsManager = PositionModel::where('title', 'Operations Manager')->first();
        $financeManager = PositionModel::where('title', 'Finance & Compliance Lead')->first();
        $supportAgent = PositionModel::where('title', 'Member Support Agent')->first();
        
        if ($opsManager) {
            DB::table('hiring_roadmap')->insert([
                'position_id' => $opsManager->id,
                'phase' => 'phase_1',
                'target_hire_date' => now()->addDays(30),
                'priority' => 'critical',
                'headcount' => 1,
                'status' => 'planned',
                'budget_allocated' => 20000.00,
                'notes' => 'PRIORITY #1 HIRE - Most critical position to free up operational time',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
        
        if ($financeManager) {
            DB::table('hiring_roadmap')->insert([
                'position_id' => $financeManager->id,
                'phase' => 'phase_1',
                'target_hire_date' => now()->addDays(30),
                'priority' => 'critical',
                'headcount' => 1,
                'status' => 'planned',
                'budget_allocated' => 15000.00,
                'notes' => 'Essential for financial controls and compliance',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
        
        if ($supportAgent) {
            DB::table('hiring_roadmap')->insert([
                'position_id' => $supportAgent->id,
                'phase' => 'phase_1',
                'target_hire_date' => now()->addDays(30),
                'priority' => 'high',
                'headcount' => 2,
                'status' => 'planned',
                'budget_allocated' => 8000.00,
                'notes' => 'Build support capacity for growing member base',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
}
