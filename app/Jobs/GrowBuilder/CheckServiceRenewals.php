<?php

namespace App\Jobs\GrowBuilder;

use App\Models\AgencyClientService;
use App\Notifications\GrowBuilder\ServiceRenewalReminder;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CheckServiceRenewals implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('CheckServiceRenewals job started');

        // Check for services due for renewal in 30, 15, and 7 days
        $reminderDays = [30, 15, 7];

        foreach ($reminderDays as $days) {
            $targetDate = Carbon::now()->addDays($days)->format('Y-m-d');

            $services = AgencyClientService::where('status', 'active')
                ->whereNotNull('renewal_date')
                ->whereDate('renewal_date', $targetDate)
                ->with(['client.agency.users', 'client'])
                ->get();

            foreach ($services as $service) {
                // Notify agency users about upcoming renewal
                $agency = $service->client->agency;
                
                if ($agency && $agency->users) {
                    foreach ($agency->users as $user) {
                        $user->notify(new ServiceRenewalReminder($service, $days));
                    }
                }

                Log::info("Service renewal reminder sent", [
                    'service_id' => $service->id,
                    'service_name' => $service->service_name,
                    'client' => $service->client->client_name,
                    'days_until_renewal' => $days,
                ]);
            }
        }

        Log::info('CheckServiceRenewals job completed');
    }
}
