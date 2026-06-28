<?php

namespace App\Application\Services;

use App\Infrastructure\Persistence\Eloquent\EmailMarketing\EmailQueueModel;
use App\Infrastructure\Persistence\Eloquent\EmailMarketing\EmailTrackingModel;
use App\Infrastructure\Persistence\Eloquent\EmailMarketing\EmailTemplateModel;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailQueueService
{
    public function __construct(
        private EmailTemplateService $templateService
    ) {}

    public function sendQueuedEmails(int $batchSize = 100): int
    {
        $emails = EmailQueueModel::with(['user', 'template'])
            ->where('status', 'pending')
            ->where('scheduled_at', '<=', now())
            ->limit($batchSize)
            ->get();

        $sent = 0;

        foreach ($emails as $email) {
            try {
                $this->sendEmail($email);
                $sent++;
            } catch (\Exception $e) {
                Log::error('Email send failed', [
                    'queue_id' => $email->id,
                    'user_id' => $email->user_id,
                    'error' => $e->getMessage(),
                ]);

                $email->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
            }
        }

        return $sent;
    }

    private function sendEmail(EmailQueueModel $queueItem): void
    {
        $queueItem->update(['status' => 'sending']);

        $user = $queueItem->user;
        $template = $queueItem->template;

        // Render template with user data
        $html = $this->templateService->renderTemplate($template, $user);
        $subject = $this->templateService->renderSubject($template->subject, $user);

        // Send email
        Mail::send([], [], function ($message) use ($user, $subject, $html) {
            $message->to($user->email, $user->name)
                ->subject($subject)
                ->html($html);
        });

        // Update queue item
        $queueItem->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        // Track sent event
        EmailTrackingModel::create([
            'queue_id' => $queueItem->id,
            'user_id' => $user->id,
            'campaign_id' => $queueItem->campaign_id,
            'event_type' => 'sent',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function retryFailed(): int
    {
        $failed = EmailQueueModel::where('status', 'failed')
            ->where('created_at', '>', now()->subDays(7))
            ->get();

        $retried = 0;

        foreach ($failed as $email) {
            $email->update([
                'status' => 'pending',
                'error_message' => null,
            ]);
            $retried++;
        }

        return $retried;
    }

    public function cancelScheduled(int $queueId): void
    {
        EmailQueueModel::where('id', $queueId)
            ->where('status', 'pending')
            ->update(['status' => 'cancelled']);
    }
}
