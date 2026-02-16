<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Infrastructure\Persistence\Eloquent\CMS\CompanyModel;
use App\Infrastructure\Persistence\Eloquent\CMS\SkillModel;

class DefaultSkillsSeeder extends Seeder
{
    public function run(): void
    {
        $companies = CompanyModel::all();

        $defaultSkills = [
            // Technical Skills
            ['name' => 'Microsoft Office Suite', 'category' => 'technical', 'level_required' => 'basic', 'is_core' => true],
            ['name' => 'Data Analysis', 'category' => 'technical', 'level_required' => 'intermediate', 'is_core' => false],
            ['name' => 'Project Management', 'category' => 'technical', 'level_required' => 'intermediate', 'is_core' => false],
            ['name' => 'Financial Reporting', 'category' => 'technical', 'level_required' => 'intermediate', 'is_core' => false],
            ['name' => 'Database Management', 'category' => 'technical', 'level_required' => 'advanced', 'is_core' => false],
            
            // Soft Skills
            ['name' => 'Communication', 'category' => 'soft_skills', 'level_required' => 'intermediate', 'is_core' => true],
            ['name' => 'Teamwork', 'category' => 'soft_skills', 'level_required' => 'intermediate', 'is_core' => true],
            ['name' => 'Problem Solving', 'category' => 'soft_skills', 'level_required' => 'intermediate', 'is_core' => true],
            ['name' => 'Time Management', 'category' => 'soft_skills', 'level_required' => 'basic', 'is_core' => true],
            ['name' => 'Customer Service', 'category' => 'soft_skills', 'level_required' => 'intermediate', 'is_core' => false],
            
            // Leadership Skills
            ['name' => 'Team Leadership', 'category' => 'leadership', 'level_required' => 'intermediate', 'is_core' => false],
            ['name' => 'Strategic Planning', 'category' => 'leadership', 'level_required' => 'advanced', 'is_core' => false],
            ['name' => 'Decision Making', 'category' => 'leadership', 'level_required' => 'intermediate', 'is_core' => false],
            ['name' => 'Conflict Resolution', 'category' => 'leadership', 'level_required' => 'intermediate', 'is_core' => false],
            
            // Language Skills
            ['name' => 'English (Business)', 'category' => 'language', 'level_required' => 'intermediate', 'is_core' => true],
            ['name' => 'Report Writing', 'category' => 'language', 'level_required' => 'intermediate', 'is_core' => false],
        ];

        foreach ($companies as $company) {
            foreach ($defaultSkills as $skill) {
                try {
                    SkillModel::firstOrCreate(
                        [
                            'company_id' => $company->id,
                            'name' => $skill['name'],
                        ],
                        [
                            'description' => "Default {$skill['name']} skill",
                            'category' => $skill['category'],
                            'level_required' => $skill['level_required'],
                            'is_core' => $skill['is_core'],
                        ]
                    );
                } catch (\Exception $e) {
                    $this->command->warn("Skipped duplicate skill: {$skill['name']} for company {$company->id}");
                }
            }
        }

        $this->command->info('Default skills seeded successfully for all companies');
    }
}
