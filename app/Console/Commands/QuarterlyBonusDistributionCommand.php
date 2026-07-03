<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class QuarterlyBonusDistributionCommand extends Command
{
    protected $signature = "bonus:distribute-quarterly";
    protected $description = "Distribute quarterly bonuses to eligible members";

    public function handle(): int
    {
        $this->info("Quarterly bonus distribution not yet implemented.");
        return Command::SUCCESS;
    }
}