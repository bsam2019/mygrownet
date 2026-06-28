<?php

namespace App\Application\UseCases\EmailMarketing;

use App\Domain\EmailMarketing\Entities\EmailCampaign;
use App\Domain\EmailMarketing\Services\CampaignEnrollmentService;
use App\Infrastructure\Persistence\Eloquent\EmailMarketing\CampaignSubscriberModel;
use App\Infrastructure\Persistence\Eloquent\EmailMarketing\EmailQueueModel;
use App\Infrastructure\Persistence\Eloquent\EmailMarketing\EmailSequenceModel;
use App\Models\User;
use Carbon\Carbon;

class EnrollUserInCampaignUseCase
{
    public function __construct(
        private CampaignEnrollmentService $enrollmentService
    ) {}

    public function execute(EmailCampaign $campaign, User $user): void
    {
        // Validate enrollment eligibility
        if (!$this->enrollmentService->canEnroll($campaign, $user)) {
            throw new \DomainException('User cannot be enrolled in this campaign');
        }

        // Create subscriber record
        $subscriber = CampaignSubscriberModel::create([
            'campaign_id' => $campaign->id()->value(),
            'user_id' => $user->id,
            'status' => 'enrolled',
            'enrolled_at' => now(),
            'metadata' => [
                'enrolled_from' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ],
        ]);

        // Schedule all sequence emails
        $this->scheduleSequenceEmails($campaign, $subscriber, $user);
    }

    private function scheduleSequenceEmails(EmailCampaign $campaign, CampaignSubscriberModel $subscriber, User $user): void
    {
        $sequences = EmailSequenceModel::where('campaign_id', $campaign->id()->value())
            ->where('is_active', true)
            ->orderBy('sequence_order')
            ->get();

        $enrolledAt = Carbon::parse($subscriber->enrolled_at);

        foreach ($sequences as $sequence) {
            $scheduledAt = $enrolledAt
                ->copy()
                ->addDays($sequence->delay_days)
                ->addHours($sequence->delay_hours);

            EmailQueueModel::create([
                'campaign_id' => $campaign->id()->value(),
                'sequence_id' => $sequence->id,
                'subscriber_id' => $subscriber->id,
                'user_id' => $user->id,
                'template_id' => $sequence->template_id,
                'scheduled_at' => $scheduledAt,
                'status' => 'pending',
            ]);
        }
    }
}
