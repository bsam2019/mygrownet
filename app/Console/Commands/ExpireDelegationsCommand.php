<?php

namespace App\Console\Commands;

use App\Domain\Employee\Services\DelegationService;
use Illuminate\Console\Command;

class ExpireDelegationsCommand extends Command
{
    protected $signature = 'delegations:expire';
    protected $description = 'Expire delegations that have passed their expiration date';

    public function handle(DelegationService $delegationService): int
    {
        $this->info('Checking for expired delegations...');
        
        $count = $delegationService->expireOverdueDelegations();
        
        if ($count > 0) {
            $this->info("Expired {$count} delegation(s).");
        } else {
            $this->info('No delegations to expire.');
        }

        return self::SUCCESS;
    }
}
