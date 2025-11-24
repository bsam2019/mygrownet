<?php

namespace App\Console\Commands\EmailMarketing;

use Illuminate\Console\Command;
use App\Application\Services\EmailCampaignService;
use App\Domain\EmailMarketing\Repositories\EmailCampaignRepository;
use App\Domain\EmailMarketing\ValueObjects\CampaignType;
use App\Models\User;

class EnrollInactiveUsers extends Command
{
    protected $signature = 'email:enroll-inactive-users';

    protected $description = 'Enroll inactive users in re-activation campaigns';

    public function __construct(
        private EmailCampaignRepository $campaignRepository,
        private EmailCampaignService $campaignService
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('Finding inactive users...');

        // Find reactivation campaign
        $campaign = $this->campaignRepository->findActiveByType(CampaignType::reactivation());

        if (!$campaign) {
            $this->warn('No active reactivation campaign found');
            return Command::FAILURE;
        }

        // Find users inactive for 30+ days
        $inactiveUsers = User::where('last_login_at', '<', now()->subDays(30))
            ->whereDoesntHave('campaignSubscribers', function ($query) use ($campaign) {
                $query->where('campaign_id', $campaign->id()->value())
                    ->whereIn('status', ['enrolled', 'active']);
            })
            ->limit(100)
            ->get();

        $enrolled = 0;

        foreach ($inactiveUsers as $user) {
            try {
                $this->campaignService->enrollUser($campaign->id()->value(), $user);
                $enrolled++;
            } catch (\Exception $e) {
                $this->error("Failed to enroll user {$user->id}: {$e->getMessage()}");
            }
        }

        $this->info("✅ Enrolled {$enrolled} inactive users in reactivation campaign");

        return Command::SUCCESS;
    }
}

