<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MergeDuplicateUsers extends Command
{
    protected $signature = 'stockflow:merge-users
                            {--dry-run : Show what would be merged without making changes}
                            {--source=sa_users : Source table to merge from (sa_users or prime_edge_clients)}';

    protected $description = 'Merge users from legacy tables (sa_users, prime_edge_clients) into platform users table. Phase 8e.';

    private array $log = [];

    public function handle(): int
    {
        $source = $this->option('source');
        $dryRun = $this->option('dry-run');

        if (!in_array($source, ['sa_users', 'prime_edge_clients'])) {
            $this->error('Invalid source. Use --source=sa_users or --source=prime_edge_clients');
            return Command::FAILURE;
        }

        $this->info("Scanning {$source} for duplicate/missing platform user records...");

        $legacyUsers = DB::table($source)->get();

        $matched = 0;
        $promoted = 0;
        $ambiguous = 0;
        $skipped = 0;

        $bar = $this->output->createProgressBar(count($legacyUsers));
        $bar->start();

        foreach ($legacyUsers as $legacyUser) {
            $platformUser = User::where('email', $legacyUser->email)->first();

            if ($platformUser) {
                $result = $this->mergeMatched($legacyUser, $platformUser, $source, $dryRun);
                match ($result) {
                    'matched' => $matched++,
                    'ambiguous' => $ambiguous++,
                    default => $skipped++,
                };
            } else {
                $this->promoteToPlatformUser($legacyUser, $source, $dryRun);
                $promoted++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->table(
            ['Category', 'Count'],
            [
                ['Matched by email — relinked', (string) $matched],
                ['Promoted to platform user', (string) $promoted],
                ['Ambiguous — flagged for review', (string) $ambiguous],
                ['Skipped', (string) $skipped],
            ]
        );

        if ($ambiguous > 0) {
            $this->warn("⚠️  {$ambiguous} ambiguous case(s) require manual review.");
            $this->line('Check storage/logs for details.');
        }

        if ($dryRun) {
            $this->info('Dry run — no changes made.');
        }

        $this->info('Merge complete.');

        return Command::SUCCESS;
    }

    private function mergeMatched(object $legacyUser, User $platformUser, string $source, bool $dryRun): string
    {
        $namesMatch = strtolower(trim($legacyUser->name ?? '')) === strtolower(trim($platformUser->name));
        $sameHash = $legacyUser->password === $platformUser->password;

        if (!$namesMatch || !$sameHash) {
            $this->logAmbiguous($legacyUser, $platformUser, $source, $namesMatch, $sameHash);
            return 'ambiguous';
        }

        if (!$dryRun) {
            $this->relinkForeignKeys($legacyUser->id, $platformUser->id, $source);
            AuditLog::logEvent(
                eventType: 'user_merged',
                metadata: [
                    'source_table' => $source,
                    'legacy_id' => $legacyUser->id,
                    'platform_user_id' => $platformUser->id,
                    'email' => $legacyUser->email,
                    'action' => 'relinked',
                ],
            );
        }

        $this->log[] = "Relinked {$source}:{$legacyUser->id} → users:{$platformUser->id} ({$legacyUser->email})";

        return 'matched';
    }

    private function promoteToPlatformUser(object $legacyUser, string $source, bool $dryRun): void
    {
        if ($dryRun) {
            $this->log[] = "Would promote {$source}:{$legacyUser->id} ({$legacyUser->email}) to platform user";
            return;
        }

        $tempPassword = Str::random(40);

        $user = User::create([
            'name' => $legacyUser->name ?? 'Unknown',
            'email' => $legacyUser->email,
            'password' => Hash::make($tempPassword),
            'status' => 'active',
        ]);

        $this->relinkForeignKeys($legacyUser->id, $user->id, $source);

        AuditLog::logEvent(
            eventType: 'user_merged',
            metadata: [
                'source_table' => $source,
                'legacy_id' => $legacyUser->id,
                'platform_user_id' => $user->id,
                'email' => $legacyUser->email,
                'action' => 'promoted',
                'temp_password' => true,
            ],
        );

        $this->log[] = "Promoted {$source}:{$legacyUser->id} → users:{$user->id} ({$legacyUser->email}) [password reset required]";
    }

    private function relinkForeignKeys(int $legacyId, int $platformId, string $source): void
    {
        if ($source === 'sa_users') {
            DB::table('sa_company_users')
                ->where('user_id', $legacyId)
                ->update(['user_id' => $platformId]);
        }
    }

    private function logAmbiguous(object $legacyUser, User $platformUser, string $source, bool $namesMatch, bool $sameHash): void
    {
        $reason = [];
        if (!$namesMatch) {
            $reason[] = "name mismatch ('{$legacyUser->name}' vs '{$platformUser->name}')";
        }
        if (!$sameHash) {
            $reason[] = 'different password hash';
        }

        $message = "AMBIGUOUS: {$source}:{$legacyUser->id} ({$legacyUser->email}) — " . implode(', ', $reason);
        $this->log[] = $message;
        logger()->warning($message);
    }
}
