<?php

namespace App\Infrastructure\PrimeEdge\Repositories;

use App\Domain\PrimeEdge\Entities\Appointment;
use App\Domain\PrimeEdge\Repositories\AppointmentRepositoryInterface;
use App\Domain\PrimeEdge\ValueObjects\AppointmentId;
use App\Domain\PrimeEdge\ValueObjects\AppointmentStatus;
use App\Domain\PrimeEdge\ValueObjects\ClientId;
use App\Domain\PrimeEdge\ValueObjects\DateTimeRange;
use App\Infrastructure\PrimeEdge\Persistence\AppointmentModel;
use DateTimeImmutable;

class EloquentAppointmentRepository implements AppointmentRepositoryInterface
{
    public function save(Appointment $appointment): void
    {
        AppointmentModel::updateOrCreate(
            ['id' => $appointment->id()->toString()],
            [
                'client_id' => $appointment->clientId()->toString(),
                'title' => $appointment->title(),
                'description' => $appointment->description(),
                'start_time' => $appointment->dateTimeRange()->start(),
                'end_time' => $appointment->dateTimeRange()->end(),
                'status' => $appointment->status()->value,
                'meeting_link' => $appointment->meetingLink(),
            ]
        );
    }

    public function findById(AppointmentId $id): ?Appointment
    {
        $model = AppointmentModel::find($id->toString());
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByClientId(ClientId $clientId): array
    {
        return AppointmentModel::where('client_id', $clientId->toString())
            ->orderBy('start_time', 'desc')
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function findUpcoming(): array
    {
        return AppointmentModel::whereIn('status', ['scheduled', 'confirmed'])
            ->where('start_time', '>=', now())
            ->orderBy('start_time')
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function findAll(): array
    {
        return AppointmentModel::orderBy('start_time', 'desc')
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function nextId(): AppointmentId
    {
        return AppointmentId::generate();
    }

    private function toDomainEntity(AppointmentModel $model): Appointment
    {
        return Appointment::reconstitute(
            id: AppointmentId::fromString($model->id),
            clientId: ClientId::fromString($model->client_id),
            title: $model->title,
            description: $model->description,
            dateTimeRange: DateTimeRange::fromDateTimes(
                new DateTimeImmutable($model->start_time->toDateTimeString()),
                new DateTimeImmutable($model->end_time->toDateTimeString()),
            ),
            status: AppointmentStatus::from($model->status),
            meetingLink: $model->meeting_link,
            createdAt: new DateTimeImmutable($model->created_at->toDateTimeString()),
            updatedAt: $model->updated_at ? new DateTimeImmutable($model->updated_at->toDateTimeString()) : null,
        );
    }
}
