<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReverseInvalidWithdrawals extends Command
{
    protected $signature = 'withdrawals:reverse-invalid {user_id?}';
    protected $description = 'Reverse withdrawals made from invalid commissions (users without starter kit)';

    public function handle()
    {
        $userId = $this->argument('user_id');
        
        if ($userId) {
            $this->reverseForUser($userId);
        } else {
            $this->info('Finding users who withdrew from invalid commissions...');
            
            // Find users without starter kit who have withdrawals
            $users = User::where('has_starter_kit', false)
                ->orWhereNull('has_starter_kit')
                ->whereHas('withdrawals', function($query) {
                    $query->where('status', 'completed');
                })
                ->get();
                
            $this->info("Found {$users->count()} users with withdrawals but no starter kit");
            
            if ($users->count() == 0) {
                $this->info('No users to process');
                return 0;
            }
            
            $this->table(
                ['ID', 'Name', 'Total Withdrawn', 'Has Starter Kit'],
                $users->map(function($u) {
                    $withdrawn = $u->withdrawals()->where('status', 'completed')->sum('amount');
                    return [
                        $u->id,
                        $u->name,
                        'K' . $withdrawn,
                        $u->has_starter_kit ? 'Yes' : 'No'
                    ];
                })
            );
            
            if (!$this->confirm('Reverse withdrawals for these users?', false)) {
                $this->info('Cancelled');
                return 0;
            }
            
            foreach ($users as $user) {
                $this->reverseForUser($user->id);
            }
        }
        
        return 0;
    }
    
    private function reverseForUser($userId)
    {
        $user = User::find($userId);
        
        if (!$user) {
            $this->error("User not found: {$userId}");
            return;
        }
        
        $this->info("Processing: {$user->name} (ID: {$user->id})");
        
        // Get completed withdrawals
        $withdrawals = $user->withdrawals()->where('status', 'completed')->get();
        
        if ($withdrawals->count() == 0) {
            $this->line("  No completed withdrawals found");
            return;
        }
        
        $totalWithdrawn = $withdrawals->sum('amount');
        $this->line("  Total withdrawn: K{$totalWithdrawn}");
        
        // Check if they have starter kit
        if ($user->has_starter_kit) {
            $this->warn("  User already has starter kit - skipping");
            return;
        }
        
        // Create a reversal transaction
        DB::table('transactions')->insert([
            'user_id' => $user->id,
            'type' => 'credit',
            'amount' => $totalWithdrawn,
            'description' => "Reversal of invalid withdrawals (K{$totalWithdrawn}) - withdrawn before starter kit purchase",
            'status' => 'completed',
            'reference' => 'REVERSAL-' . now()->format('YmdHis'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $this->info("  ✓ Reversed K{$totalWithdrawn}");
        $this->line("  User can now purchase starter kit with wallet balance");
    }
}
