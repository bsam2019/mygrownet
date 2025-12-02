<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ResetUserSession extends Command
{
    protected $signature = 'user:reset-session {user_id : The ID of the user to reset}';
    protected $description = 'Reset all sessions for a specific user (forces re-login)';

    public function handle(): int
    {
        $userId = $this->argument('user_id');
        $user = User::find($userId);

        if (!$user) {
            $this->error("User with ID {$userId} not found!");
            return 1;
        }

        $this->info("Resetting session for: {$user->name} ({$user->email})");

        // Clear remember token
        $user->remember_token = null;
        $user->save();
        $this->line('✅ Remember token cleared');

        // Clear database sessions
        if (Schema::hasTable('sessions')) {
            $count = DB::table('sessions')->where('user_id', $userId)->delete();
            $this->line("✅ Deleted {$count} database session(s)");
        }

        // Clear personal access tokens
        if (Schema::hasTable('personal_access_tokens')) {
            $count = DB::table('personal_access_tokens')
                ->where('tokenable_type', User::class)
                ->where('tokenable_id', $userId)
                ->delete();
            $this->line("✅ Deleted {$count} personal access token(s)");
        }

        $this->newLine();
        $this->info("Session reset complete! User will need to log in again.");

        return 0;
    }
}
