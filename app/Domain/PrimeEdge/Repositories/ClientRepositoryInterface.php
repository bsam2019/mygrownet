<?php

namespace App\Domain\PrimeEdge\Repositories;

use App\Domain\PrimeEdge\Entities\Client;
use App\Domain\PrimeEdge\ValueObjects\ClientId;
use App\Domain\PrimeEdge\ValueObjects\Email;

interface ClientRepositoryInterface
{
    public function save(Client $client): void;
    public function findById(ClientId $id): ?Client;
    public function findByEmail(Email $email): ?Client;
    public function findAll(): array;
    public function findByStatus(string $status): array;
    public function nextId(): ClientId;
}
