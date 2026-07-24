<?php

namespace App\Domain\Ubumi\Services;

use App\Domain\Ubumi\Entities\CheckIn;
use App\Domain\Ubumi\Repositories\FamilyRepositoryInterface;
use App\Domain\Ubumi\Repositories\PersonRepositoryInterface;
use App\Domain\Ubumi\ValueObjects\FamilyId;
use App\Domain\Ubumi\ValueObjects\PersonId;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\DB;

class AlertService
{
    public function __construct(
        private readonly FamilyRepositoryInterface $familyRepository,
        private readonly PersonRepositoryInterface $personRepository
    ) {}

    public function processCheckIn(CheckIn $checkIn): void
    {
        if (!$checkIn->requiresAlert()) {
            return;
        }

        $person = $this->personRepository->findById($checkIn->personId());
        if (!$person) {
            return;
        }

        $family = $this->familyRepository->findById(FamilyId::fromString($person->getFamilyId()));
        if (!$family) {
            return;
        }

        $alertType = match($checkIn->status()->value) {
            'unwell' => 'unwell',
            'need_assistance' => 'need_assistance',
            default => null,
        };

        if (!$alertType) {
            return;
        }

        $message = $this->generateAlertMessage($person->getName()->toString(), $checkIn);

        $alertId = DB::table('ubumi_alerts')->insertGetId([
            'id' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
            'family_id' => $family->getId()->toString(),
            'person_id' => $person->getId()->toString(),
            'check_in_id' => $checkIn->id()->value(),
            'alert_type' => $alertType,
            'message' => $message,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->notifyFamilyAdmin($family->getAdminUserId(), $message, $person->getName()->toString());

        $this->notifyCaregivers($person->getId()->toString(), $message, $person->getName()->toString());
    }

    public function getPendingAlertsByFamily(FamilyId $familyId): array
    {
        return DB::table('ubumi_alerts')
            ->where('family_id', $familyId->toString())
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($alert) => [
                'id' => $alert->id,
                'message' => $alert->message,
                'created_at' => $alert->created_at,
            ])
            ->toArray();
    }

    public function acknowledgeAlert(string $alertId, int $userId): void
    {
        DB::table('ubumi_alerts')
            ->where('id', $alertId)
            ->update([
                'status' => 'acknowledged',
                'acknowledged_by' => $userId,
                'acknowledged_at' => now(),
                'updated_at' => now(),
            ]);
    }

    public function checkMissedCheckIns(): void
    {
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
        $person = $this->personRepository->findById(PersonId::fromString($personId));
        if (!$person) {
            return;
        }

        $family = $this->familyRepository->findById(FamilyId::fromString($person->getFamilyId()));
        if (!$family) {
            return;
        }

        $message = sprintf(
            '%s has not checked in for %d hours. Please reach out to ensure they are okay.',
            $person->getName()->toString(),
            $hoursMissed
        );

        DB::table('ubumi_alerts')->insert([
            'id' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
            'family_id' => $family->getId()->toString(),
            'person_id' => $person->getId()->toString(),
            'check_in_id' => null,
            'alert_type' => 'missed_checkin',
            'message' => $message,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->notifyFamilyAdmin($family->getAdminUserId(), $message, $person->getName()->toString());
        $this->notifyCaregivers($personId, $message, $person->getName()->toString());
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

        $admin->notify(new \App\Notifications\Ubumi\CheckInAlertNotification($message, $personName));
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