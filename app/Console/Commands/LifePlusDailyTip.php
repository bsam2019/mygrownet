<?php

namespace App\Console\Commands;

use App\Domain\LifePlus\Services\KnowledgeService;
use App\Domain\LifePlus\Services\NotificationService;
use Illuminate\Console\Command;

class LifePlusDailyTip extends Command
{
    protected $signature = 'lifeplus:daily-tip';
    protected $description = 'Send daily tip notification to all Life+ users';

    public function handle(KnowledgeService $knowledgeService, NotificationService $notificationService): int
    {
        $this->info('Fetching daily tip...');

        $tip = $knowledgeService->getDailyTip();

        if (!$tip) {
            $this->warn('No daily tip available');
            return self::FAILURE;
        }

        $this->info("Sending tip: {$tip['title']}");

        $content = $tip['content'] ? strip_tags(substr($tip['content'], 0, 200)) : $tip['title'];
        $sent = $notificationService->sendDailyTip($content);

        $this->info("Daily tip sent to {$sent} users");

        return self::SUCCESS;
    }
}
