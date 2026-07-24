<?php

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\ZamStay\ZamStayPropertyModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ZamStayCoordinatesSeeder extends Seeder
{
    public function run(): void
    {
        $coords = [
            'Lusaka'      => [-15.3875, 28.3228],
            'Kafue'       => [-15.7667, 28.1833],
            'Ndola'       => [-12.9587, 28.6366],
            'Kitwe'       => [-12.8244, 28.2174],
            'Livingstone' => [-17.8499, 25.8606],
            'Choma'       => [-16.8100, 26.9833],
            'Chipata'     => [-13.6467, 32.6467],
            'Mfuwe'       => [-13.0733, 31.7583],
            'Kasama'      => [-10.2125, 31.1806],
            'Mongu'       => [-15.2500, 23.1333],
            'Senanga'     => [-16.1167, 23.2667],
            'Mpika'       => [-11.8333, 31.4500],
            'Nakonde'     => [-9.3333, 32.7667],
            'Mansa'       => [-11.2000, 28.8833],
            'Solwezi'     => [-12.1833, 26.4000],
            'Kabwe'       => [-14.4333, 28.4500],
            'Serenje'     => [-13.2500, 30.2333],
        ];

        $updated = 0;
        ZamStayPropertyModel::chunk(50, function ($properties) use ($coords, &$updated) {
            foreach ($properties as $property) {
                if (isset($coords[$property->location])) {
                    [$lat, $lng] = $coords[$property->location];
                    DB::table('zamstay_properties')
                        ->where('id', $property->id)
                        ->update(['latitude' => $lat, 'longitude' => $lng]);
                    $updated++;
                }
            }
        });

        $this->command->info("Updated coordinates for {$updated} properties.");
    }
}
