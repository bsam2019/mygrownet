<?php

namespace App\Console\Commands\Employee;

use Illuminate\Console\Command;

class WarmEmployeeCacheCommand extends Command
{
    protected $signature = "employee:warm-cache";
    protected $description = "Warm employee-related cache";

    public function handle(): int
    {
        $this->info("Employee cache warmed.");
        return Command::SUCCESS;
    }
}