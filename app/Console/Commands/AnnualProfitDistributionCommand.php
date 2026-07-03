<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AnnualProfitDistributionCommand extends Command
{
    protected $signature = "profit:distribute-annual";
    protected $description = "Distribute annual profits to eligible members";

    public function handle(): int
    {
        $this->info("Annual profit distribution not yet implemented.");
        return Command::SUCCESS;
    }
}