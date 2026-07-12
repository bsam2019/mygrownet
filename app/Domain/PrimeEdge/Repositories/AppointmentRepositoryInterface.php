<?php

namespace App\Domain\PrimeEdge\Repositories;

use App\Domain\PrimeEdge\Entities\Appointment;
use App\Domain\PrimeEdge\ValueObjects\AppointmentId;
use App\Domain\PrimeEdge\ValueObjects\ClientId;

interface AppointmentRepositoryInterface
{
    public function save(Appointment $appointment): void;
    public function findById(AppointmentId $id): ?Appointment;
    public function findByClientId(ClientId $clientId): array;
    public function findUpcoming(): array;
    public function findAll(): array;
    public function nextId(): AppointmentId;
}
