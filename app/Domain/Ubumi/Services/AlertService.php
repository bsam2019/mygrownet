<?php

namespace App\Domain\Ubumi\Services;

use App\Domain\Ubumi\Entities\CheckIn;
use App\Domain\Ubumi\Repositories\FamilyRepositoryInterface;
use App\Domain\Ubumi\Repositories\PersonRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\DB;

class AlertService
{
    public function __construct(
        private readonly FamilyRepositoryInterface $familyRepository,
        private readonly PersonRepositoryInterface $personRepository
    ) {}

    /**
     * Process check-in and create alerts if needed
     */
    public function processCheckIn(CheckIn $checkIn): void
    {
        if (!$checkIn->requiresAlert()) {
            return;
        }

        $person = $this->personRepository->findById($checkIn->personId());
        if (!$person) {
            return;
        }

        $family = $this->familyRepository->findById($person->familyId());
        if (!$family) {
            return;
        }

        // Create alert
        $alertType = match($checkIn->status()->value) {
            'unwell' => 'unwell',
            'need_assistance' => 'need_assistance',
            default => null,
        };

        if (!$alertType) {
            return;
        }

        $message = $this->generateAlertMessage($person->name()->fullName(), $checkIn);

        $alertId = DB::table('ubumi_alerts')->insertGetId([
            'id' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
            'family_id' => $family->id()->value(),
            'person_id' => $person->id()->value(),
            'check_in_id' => $checkIn->id()->value(),
            'alert_type' => $alertType,
            'message' => $message,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Notify family admin
        $this->notifyFamilyAdmin($family->adminUserId(), $message, $person->name()->fullName());

        // Notify caregivers
        $this->notifyCaregivers($person->id()->value(), $message, $person->name()->fullName());
    }

    /**
     * Check for missed check-ins and create alerts
     */
    public function checkMissedCheckIns(): void
    {
        // Get all persons with check-in settings
        $settings = DB::table('ubumi_check_in_settings')
            ->where('reminders_enabled', true)
            ->get();

        foreach ($settings as $setting) {
            $lastCheckIn = DB::table('ubumi_check_ins')
                ->where('person_id', $setting->person_id)
                ->orderBy('checked_in_at', 'desc')
                ->first();

            if (!$lastCheckIn) {
                continue;
            }

            $hoursSinceLastCheckIn = now()->diffInHours($lastCheckIn->checked_in_at);

            // Check if threshold exceeded and no pending alert exists
            if ($hoursSinceLastCheckIn >= $setting->missed_threshold_hours) {
                $existingAlert = DB::table('ubumi_alerts')
                    ->where('person_id', $setting->person_id)
                    ->where('alert_type', 'missed_checkin')
                    ->where('status', 'pending')
                    ->exists();

                if (!$existingAlert) {
                    $this->createMissedCheckInAlert($setting->person_id, $hoursSinceLastCheckIn);
                }
            }
        }
    }

    private function createMissedCheckInAlert(string $personId, int $hoursMissed): void
    {
        $person = $this->personRepository->findById(\App\Domain\Ubumi\ValueObjects\PersonId::fromString($personId));
        if (!$person) {
            return;
        }

        $family = $this->familyRepository->findById($person->familyId());
        if (!$family) {
            return;
        }

        $message = sprintf(
            '%s has not checked in for %d hours. Please reach out to ensure they are okay.',
            $person->name()->fullName(),
            $hoursMissed
        );

        DB::table('ubumi_alerts')->insert([
            'id' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
            'family_id' => $family->id()->value(),
            'person_id' => $person->id()->value(),
            'check_in_id' => null,
            'alert_type' => 'missed_checkin',
            'message' => $message,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->notifyFamilyAdmin($family->adminUserId(), $message, $person->name()->fullName());
        $this->notifyCaregivers($personId, $message, $person->name()->fullName());
    }

    private function generateAlertMessage(string $personName, CheckIn $checkIn): string
    {
        $baseMessage = match($checkIn->status()->value) {
            'unwell' => sprintf('%s reported feeling unwell.', $personName),
            'need_assistance' => sprintf('%s needs assistance!', $personName),
            default => sprintf('%s checked in.', $personName),
        };

        if ($checkIn->note()) {
            $baseMessage .= ' Note: ' . $checkIn->note();
        }

        return $baseMessage;
    }

    private function notifyFamilyAdmin(int $adminUserId, string $message, string $personName): void
    {
        $admin = User::find($adminUserId);
        if (!$admin) {
            return;
        }

        // Send in-app notification
        $admin->notify(new \App\Notifications\Ubumi\CheckInAlertNotification($message, $personName));

        // TODO: Send SMS if enabled
        // TODO: Send email if enabled
    }

    private function notifyCaregivers(string $personId, string $message, string $personName): void
    {
        $caregivers = DB::table('ubumi_caregivers')
            ->where('person_id', $personId)
            ->where('receives_alerts', true)
            ->get();

        foreach ($caregivers as $caregiver) {
            $user = User::find($caregiver->caregiver_user_id);
            if ($user) {
                $user->notify(new \App\Notifications\Ubumi\CheckInAlertNotification($message, $personName));
            }
        }
    }
}
