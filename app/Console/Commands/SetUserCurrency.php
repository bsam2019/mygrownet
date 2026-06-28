<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class SetUserCurrency extends Command
{
    protected $signature = 'user:set-currency {user_id} {currency=USD}';
    protected $description = 'Set a user\'s base currency (ZMW or USD) for testing';

    public function handle()
    {
        $userId = $this->argument('user_id');
        $currency = strtoupper($this->argument('currency'));

        if (!in_array($currency, ['ZMW', 'USD'])) {
            $this->error('Currency must be ZMW or USD');
            return 1;
        }

        $user = User::find($userId);
        if (!$user) {
            $this->error("User #{$userId} not found");
            return 1;
        }

        $user->user_currency = $currency;
        $user->preferred_currency = $currency;
        $user->save();

        $this->info("✓ User #{$userId} ({$user->name}) currency set to {$currency}");
        $this->info("  Wallet balance: {$user->balance} (stored in {$currency})");
        
        return 0;
    }
}
