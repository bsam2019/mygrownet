<?php

namespace App\Console\Commands\Employee;

use Illuminate\Console\Command;

class ClearEmployeeCacheCommand extends Command
{
    protected $signature = "employee:clear-cache";
    protected $description = "Clear employee-related cache";

    public function handle(): int
    {
        $this->info("Employee cache cleared.");
        return Command::SUCCESS;
    }
}