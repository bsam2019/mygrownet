<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\LevelAdvancementService;
use Illuminate\Console\Command;

class CheckLevelAdvancements extends Command
{
    protected $signature = 'points:check-advancements';
    protected $description = 'Check all users for level advancement eligibility';

    public function handle(LevelAdvancementService $levelService): int
    {
        $this->info('Checking level advancements for all users...');

        $usersChecked = 0;
        $usersAdvanced = 0;

        User::with('points')->chunk(100, function ($users) use ($levelService, &$usersChecked, &$usersAdvanced) {
            foreach ($users as $user) {
                $newLevel = $levelService->checkLevelAdvancement($user);
                
                if ($newLevel) {
                    $this->info("✓ {$user->name} advanced to {$newLevel}");
                    $usersAdvanced++;
                }

                $usersChecked++;
            }
        });

        $this->info("✓ Checked {$usersChecked} users");
        $this->info("🎉 {$usersAdvanced} users advanced");

        return Command::SUCCESS;
    }
}
