<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Repositories;

use App\Domain\StockFlow\Entities\User;
use App\Domain\StockFlow\ValueObjects\UserId;

interface UserRepositoryInterface
{
    public function findById(UserId $id): ?User;

    public function findByEmail(string $email): ?User;

    public function findAll(): array;

    public function save(User $user): User;
}
