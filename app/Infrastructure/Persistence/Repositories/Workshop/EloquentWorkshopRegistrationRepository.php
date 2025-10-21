<?php

namespace App\Infrastructure\Persistence\Repositories\Workshop;

use App\Domain\Workshop\Entities\WorkshopRegistration;
use App\Domain\Workshop\Repositories\WorkshopRegistrationRepository;
use App\Infrastructure\Persistence\Eloquent\Workshop\WorkshopRegistrationModel;
use DateTimeImmutable;

class EloquentWorkshopRegistrationRepository implements WorkshopRegistrationRepository
{
    public function save(WorkshopRegistration $registration): WorkshopRegistration
    {
        $data = [
            'workshop_id' => $registration->workshopId(),
            'user_id' => $registration->userId(),
            'payment_id' => $registration->paymentId(),
            'status' => $registration->status(),
            'registration_notes' => $registration->registrationNotes(),
            'attended_at' => $registration->attendedAt()?->format('Y-m-d H:i:s'),
            'completed_at' => $registration->completedAt()?->format('Y-m-d H:i:s'),
            'certificate_issued' => $registration->certificateIssued(),
            'certificate_issued_at' => $registration->certificateIssuedAt()?->format('Y-m-d H:i:s'),
            'points_awarded' => $registration->pointsAwarded(),
            'points_awarded_at' => $registration->pointsAwardedAt()?->format('Y-m-d H:i:s'),
        ];

        if ($registration->id()) {
            $model = WorkshopRegistrationModel::findOrFail($registration->id());
            $model->update($data);
        } else {
            $model = WorkshopRegistrationModel::create($data);
        }

        return $this->toDomainEntity($model);
    }

    public function findById(int $id): ?WorkshopRegistration
    {
        $model = WorkshopRegistrationModel::find($id);
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByWorkshopAndUser(int $workshopId, int $userId): ?WorkshopRegistration
    {
        $model = WorkshopRegistrationModel::where('workshop_id', $workshopId)
            ->where('user_id', $userId)
            ->first();
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByWorkshop(int $workshopId): array
    {
        return WorkshopRegistrationModel::where('workshop_id', $workshopId)
            ->get()
            ->map(fn($model) => $this->toDomainEntity($model))
            ->toArray();
    }

    public function findByUser(int $userId): array
    {
        return WorkshopRegistrationModel::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($model) => $this->toDomainEntity($model))
            ->toArray();
    }

    public function countByWorkshop(int $workshopId): int
    {
        return WorkshopRegistrationModel::where('workshop_id', $workshopId)
            ->whereIn('status', ['registered', 'attended', 'completed'])
            ->count();
    }

    private function toDomainEntity(WorkshopRegistrationModel $model): WorkshopRegistration
    {
        return new WorkshopRegistration(
            id: $model->id,
            workshopId: $model->workshop_id,
            userId: $model->user_id,
            paymentId: $model->payment_id,
            status: $model->status,
            registrationNotes: $model->registration_notes,
            attendedAt: $model->attended_at ? new DateTimeImmutable($model->attended_at) : null,
            completedAt: $model->completed_at ? new DateTimeImmutable($model->completed_at) : null,
            certificateIssued: $model->certificate_issued,
            certificateIssuedAt: $model->certificate_issued_at ? new DateTimeImmutable($model->certificate_issued_at) : null,
            pointsAwarded: $model->points_awarded,
            pointsAwardedAt: $model->points_awarded_at ? new DateTimeImmutable($model->points_awarded_at) : null,
            createdAt: new DateTimeImmutable($model->created_at),
            updatedAt: new DateTimeImmutable($model->updated_at)
        );
    }
}
