<?php

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\CMS\CompanyModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ZambianPublicHolidaysSeeder extends Seeder
{
    public function run(): void
    {
        $companies = CompanyModel::all();
        $year = date('Y');

        // Zambian Public Holidays (fixed dates)
        $holidays = [
            ['name' => "New Year's Day", 'date' => "$year-01-01", 'recurring' => true],
            ['name' => 'Youth Day', 'date' => "$year-03-12", 'recurring' => true],
            ['name' => 'Africa Freedom Day', 'date' => "$year-05-25", 'recurring' => true],
            ['name' => 'Heroes Day', 'date' => "$year-07-01", 'recurring' => true],
            ['name' => 'Unity Day', 'date' => "$year-07-02", 'recurring' => true],
            ['name' => "Farmers' Day", 'date' => "$year-08-05", 'recurring' => true],
            ['name' => 'Independence Day', 'date' => "$year-10-24", 'recurring' => true],
            ['name' => 'Christmas Day', 'date' => "$year-12-25", 'recurring' => true],
            
            // Moveable holidays (approximate dates - should be updated annually)
            ['name' => 'Good Friday', 'date' => "$year-04-18", 'recurring' => false],
            ['name' => 'Easter Monday', 'date' => "$year-04-21", 'recurring' => false],
        ];

        foreach ($companies as $company) {
            foreach ($holidays as $holiday) {
                DB::table('cms_public_holidays')->updateOrInsert(
                    [
                        'company_id' => $company->id,
                        'holiday_date' => $holiday['date'],
                    ],
                    [
                        'company_id' => $company->id,
                        'holiday_name' => $holiday['name'],
                        'holiday_date' => $holiday['date'],
                        'year' => $year,
                        'description' => 'Zambian Public Holiday',
                        'is_recurring' => $holiday['recurring'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        $this->command->info("Zambian public holidays for $year seeded successfully!");
    }
}
