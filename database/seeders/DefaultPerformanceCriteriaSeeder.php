<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Infrastructure\Persistence\Eloquent\CMS\PerformanceCriteriaModel;
use App\Infrastructure\Persistence\Eloquent\CMS\CompanyModel;

class DefaultPerformanceCriteriaSeeder extends Seeder
{
    public function run(): void
    {
        $companies = CompanyModel::all();

        $defaultCriteria = [
            [
                'criteria_name' => 'Job Knowledge & Skills',
                'description' => 'Demonstrates required technical knowledge and skills for the role',
                'category' => 'technical',
                'weight_percentage' => 20,
            ],
            [
                'criteria_name' => 'Quality of Work',
                'description' => 'Produces accurate, thorough, and high-quality work',
                'category' => 'quality',
                'weight_percentage' => 15,
            ],
            [
                'criteria_name' => 'Productivity & Efficiency',
                'description' => 'Completes work in a timely manner and manages time effectively',
                'category' => 'productivity',
                'weight_percentage' => 15,
            ],
            [
                'criteria_name' => 'Communication Skills',
                'description' => 'Communicates clearly and effectively with team and stakeholders',
                'category' => 'communication',
                'weight_percentage' => 10,
            ],
            [
                'criteria_name' => 'Teamwork & Collaboration',
                'description' => 'Works well with others and contributes to team success',
                'category' => 'teamwork',
                'weight_percentage' => 10,
            ],
            [
                'criteria_name' => 'Problem Solving',
                'description' => 'Identifies issues and develops effective solutions',
                'category' => 'technical',
                'weight_percentage' => 10,
            ],
            [
                'criteria_name' => 'Initiative & Proactivity',
                'description' => 'Takes initiative and goes beyond basic job requirements',
                'category' => 'behavioral',
                'weight_percentage' => 10,
            ],
            [
                'criteria_name' => 'Reliability & Dependability',
                'description' => 'Consistently meets commitments and deadlines',
                'category' => 'behavioral',
                'weight_percentage' => 10,
            ],
        ];

        foreach ($companies as $company) {
            // Check if criteria already exist
            if (PerformanceCriteriaModel::where('company_id', $company->id)->exists()) {
                $this->command->warn("Performance criteria already exist for company: {$company->company_name}");
                continue;
            }

            foreach ($defaultCriteria as $criteria) {
                PerformanceCriteriaModel::create([
                    'company_id' => $company->id,
                    'criteria_name' => $criteria['criteria_name'],
                    'description' => $criteria['description'],
                    'category' => $criteria['category'],
                    'weight_percentage' => $criteria['weight_percentage'],
                    'is_active' => true,
                ]);
            }

            $this->command->info("Created default performance criteria for: {$company->company_name}");
        }
    }
}
