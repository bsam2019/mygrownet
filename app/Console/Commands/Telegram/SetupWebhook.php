<?php

namespace App\Console\Commands\Telegram;

use Illuminate\Console\Command;
use Telegram\Bot\Laravel\Facades\Telegram;

class SetupWebhook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:setup-webhook';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set up Telegram bot webhook';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Setting up Telegram webhook...');

        try {
            $url = route('telegram.webhook');
            
            $response = Telegram::setWebhook(['url' => $url]);

            if ($response) {
                $this->info("✅ Webhook set successfully!");
                $this->info("URL: {$url}");
                
                // Get webhook info
                $webhookInfo = Telegram::getWebhookInfo();
                $this->info("Webhook URL: " . $webhookInfo->getUrl());
                $this->info("Pending updates: " . $webhookInfo->getPendingUpdateCount());
            } else {
                $this->error("❌ Failed to set webhook");
            }
        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            $this->error("Make sure TELEGRAM_BOT_TOKEN is set in .env");
        }
    }
}
