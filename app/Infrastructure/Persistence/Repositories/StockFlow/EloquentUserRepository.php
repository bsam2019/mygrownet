<?php

namespace App\Infrastructure\Persistence\Repositories\StockFlow;

use App\Domain\StockFlow\Entities\User;
use App\Domain\StockFlow\Repositories\UserRepositoryInterface;
use App\Domain\StockFlow\ValueObjects\UserId;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaUserModel;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function findById(UserId $id): ?User
    {
        $model = SaUserModel::find($id->toInt());
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByEmail(string $email): ?User
    {
        $model = SaUserModel::where('email', $email)->first();
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findAll(): array
    {
        return SaUserModel::all()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function save(User $user): User
    {
        $data = [
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'password' => $user->getPassword(),
            'remember_token' => $user->getRememberToken(),
        ];

        if ($user->id() === 0) {
            $model = SaUserModel::create($data);
        } else {
            $model = SaUserModel::find($user->id());
            $model->update($data);
        }

        return $this->toDomainEntity($model->fresh());
    }

    private function toDomainEntity(SaUserModel $model): User
    {
        return User::reconstitute(
            id: UserId::fromInt($model->id),
            name: $model->name,
            email: $model->email,
            password: $model->password,
            rememberToken: $model->remember_token,
            emailVerifiedAt: $model->email_verified_at ? new \DateTimeImmutable($model->email_verified_at->format('Y-m-d H:i:s')) : null,
            createdAt: new \DateTimeImmutable($model->created_at->format('Y-m-d H:i:s')),
            updatedAt: new \DateTimeImmutable($model->updated_at->format('Y-m-d H:i:s')),
        );
    }
}
