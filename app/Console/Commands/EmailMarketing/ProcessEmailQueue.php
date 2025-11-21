<?php

namespace App\Console\Commands\EmailMarketing;

use Illuminate\Console\Command;
use App\Application\Services\EmailQueueService;

class ProcessEmailQueue extends Command
{
    protected $signature = 'email:process-queue {--batch=100 : Number of emails to process}';

    protected $description = 'Process scheduled emails in the queue';

    public function __construct(
        private EmailQueueService $queueService
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $batchSize = (int) $this->option('batch');

        $this->info("Processing email queue (batch size: {$batchSize})...");

        $sent = $this->queueService->sendQueuedEmails($batchSize);

        $this->info("✅ Sent {$sent} emails successfully");

        return Command::SUCCESS;
    }
}
