<?php

namespace App\Infrastructure\PrimeEdge\Repositories;

use App\Domain\PrimeEdge\Entities\Client;
use App\Domain\PrimeEdge\Repositories\ClientRepositoryInterface;
use App\Domain\PrimeEdge\ValueObjects\ClientId;
use App\Domain\PrimeEdge\ValueObjects\ClientName;
use App\Domain\PrimeEdge\ValueObjects\ClientStatus;
use App\Domain\PrimeEdge\ValueObjects\Email;
use App\Domain\PrimeEdge\ValueObjects\PhoneNumber;
use App\Domain\PrimeEdge\ValueObjects\BusinessType;
use App\Infrastructure\PrimeEdge\Persistence\ClientModel;
use DateTimeImmutable;

class EloquentClientRepository implements ClientRepositoryInterface
{
    public function save(Client $client): void
    {
        ClientModel::updateOrCreate(
            ['id' => $client->id()->toString()],
            [
                'name' => $client->name()->toString(),
                'email' => $client->email()->toString(),
                'password' => $client->password(),
                'phone' => $client->phone()?->toString(),
                'business_type' => $client->businessType()?->toString(),
                'company_name' => $client->companyName(),
                'status' => $client->status()->value,
                'last_login_at' => $client->lastLoginAt(),
            ]
        );
    }

    public function findById(ClientId $id): ?Client
    {
        $model = ClientModel::find($id->toString());
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByEmail(Email $email): ?Client
    {
        $model = ClientModel::where('email', $email->toString())->first();
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findAll(): array
    {
        return ClientModel::orderBy('created_at', 'desc')
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function findByStatus(string $status): array
    {
        return ClientModel::where('status', $status)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function nextId(): ClientId
    {
        return ClientId::generate();
    }

    private function toDomainEntity(ClientModel $model): Client
    {
        return Client::reconstitute(
            id: ClientId::fromString($model->id),
            name: ClientName::fromString($model->name),
            email: Email::fromString($model->email),
            password: $model->password,
            phone: $model->phone ? PhoneNumber::fromString($model->phone) : null,
            businessType: $model->business_type ? BusinessType::fromString($model->business_type) : null,
            companyName: $model->company_name,
            status: ClientStatus::from($model->status),
            createdAt: new DateTimeImmutable($model->created_at->toDateTimeString()),
            updatedAt: $model->updated_at ? new DateTimeImmutable($model->updated_at->toDateTimeString()) : null,
            lastLoginAt: $model->last_login_at ? new DateTimeImmutable($model->last_login_at->toDateTimeString()) : null,
        );
    }
}
