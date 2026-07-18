<?php

namespace Database\Seeders;

use App\Domain\Core\Models\Application;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class ApplicationRoleSeeder extends Seeder
{
    public function run(): void
    {
        $rolesByApp = [
            'stockflow'  => ['Manager', 'Viewer', 'Auditor'],
            'growfinance' => ['Accountant', 'Viewer'],
            'bizdocs'     => ['Editor', 'Viewer'],
            'grownet'     => ['Member', 'Sponsor', 'Admin'],
        ];

        foreach ($rolesByApp as $appSlug => $roleNames) {
            $app = Application::where('slug', $appSlug)->first();
            if (!$app) {
                continue;
            }

            foreach ($roleNames as $name) {
                $roleSlug = $appSlug . '-' . \Str::slug($name);
                Role::firstOrCreate(
                    ['slug' => $roleSlug],
                    ['name' => $name, 'guard_name' => 'web', 'application_id' => $app->id, 'slug' => $roleSlug],
                );
            }
        }
    }
}
