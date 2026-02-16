<?php

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\CMS\CompanyModel;
use App\Infrastructure\Persistence\Eloquent\CMS\ShiftModel;
use Illuminate\Database\Seeder;

class DefaultShiftsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = CompanyModel::all();

        foreach ($companies as $company) {
            $this->seedShiftsForCompany($company->id);
        }

        $this->command->info('Default shifts seeded for all companies.');
    }

    private function seedShiftsForCompany(int $companyId): void
    {
        $shifts = [
            [
                'shift_name' => 'Morning Shift',
                'shift_code' => 'MORNING',
                'start_time' => '08:00:00',
                'end_time' => '17:00:00',
                'break_duration_minutes' => 60,
                'grace_period_minutes' => 15,
                'overtime_threshold_minutes' => 480, // 8 hours
                'minimum_hours_full_day' => 7.5,
                'minimum_hours_half_day' => 4.0,
                'is_active' => true,
            ],
            [
                'shift_name' => 'Evening Shift',
                'shift_code' => 'EVENING',
                'start_time' => '14:00:00',
                'end_time' => '22:00:00',
                'break_duration_minutes' => 60,
                'grace_period_minutes' => 15,
                'overtime_threshold_minutes' => 480,
                'minimum_hours_full_day' => 7.5,
                'minimum_hours_half_day' => 4.0,
                'is_active' => true,
            ],
            [
                'shift_name' => 'Night Shift',
                'shift_code' => 'NIGHT',
                'start_time' => '22:00:00',
                'end_time' => '06:00:00',
                'break_duration_minutes' => 60,
                'grace_period_minutes' => 15,
                'overtime_threshold_minutes' => 480,
                'minimum_hours_full_day' => 7.5,
                'minimum_hours_half_day' => 4.0,
                'is_active' => true,
            ],
        ];

        foreach ($shifts as $shiftData) {
            // Check if shift already exists
            $exists = ShiftModel::where('company_id', $companyId)
                ->where('shift_code', $shiftData['shift_code'])
                ->exists();

            if (!$exists) {
                ShiftModel::create(array_merge($shiftData, [
                    'company_id' => $companyId,
                ]));
            }
        }
    }
}
