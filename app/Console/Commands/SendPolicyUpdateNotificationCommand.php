<?php

namespace App\Console\Commands;

use App\Infrastructure\Persistence\Eloquent\Notification\NotificationModel;
use App\Models\User;
use Illuminate\Console\Command;

class SendPolicyUpdateNotificationCommand extends Command
{
    protected $signature = 'growstream:notify-policy-update 
                            {--title= : Custom notification title} 
                            {--message= : Custom notification message} 
                            {--policy-version=August 2026 : Policy version}';

    protected $description = 'Broadcast automated policy update notifications to all users across GrowStream and Creator Hub';

    public function handle(): int
    {
        $version = $this->option('policy-version') ?? 'August 2026';
        $title = $this->option('title') ?? "GrowStream Policy Update ({$version})";
        $message = $this->option('message') ?? "We have updated our Master Terms of Service, Creator Financial & BYOP Policy, Copyright Takedown Rules, and Data Protection Policies.";

        $this->info("Broadcasting policy update notification ({$version}) to all users...");

        $count = 0;
        User::chunk(200, function ($users) use ($title, $message, &$count) {
            foreach ($users as $user) {
                NotificationModel::create([
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'notifiable_type' => User::class,
                    'notifiable_id' => $user->id,
                    'module' => 'growstream',
                    'type' => 'policy_update',
                    'category' => 'legal',
                    'title' => $title,
                    'message' => $message,
                    'action_url' => route('growstream.pages.terms'),
                    'action_text' => 'Review Terms',
                    'data' => [
                        'policy_version' => '2026-08',
                        'updated_at' => now()->toIso8601String(),
                    ],
                    'priority' => 'high',
                    'created_at' => now(),
                ]);
                $count++;
            }
        });

        $this->info("Successfully sent policy update notification to {$count} users.");

        return Command::SUCCESS;
    }
}
