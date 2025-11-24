<?php

namespace App\Infrastructure\Persistence\Repositories\Investor;

use App\Domain\Investor\Entities\InvestorAccount;
use App\Domain\Investor\Repositories\InvestorAccountRepositoryInterface;
use App\Domain\Investor\ValueObjects\InvestorStatus;
use App\Infrastructure\Persistence\Eloquent\Investor\InvestorAccountModel;
use DateTimeImmutable;

class EloquentInvestorAccountRepository implements InvestorAccountRepositoryInterface
{
    public function save(InvestorAccount $account): InvestorAccount
    {
        $data = [
            'user_id' => $account->getUserId(),
            'name' => $account->getName(),
            'email' => $account->getEmail(),
            'investment_amount' => $account->getInvestmentAmount(),
            'investment_date' => $account->getInvestmentDate()->format('Y-m-d'),
            'investment_round_id' => $account->getInvestmentRoundId(),
            'status' => $account->getStatus()->value(),
            'equity_percentage' => $account->getEquityPercentage(),
        ];

        if ($account->getId() > 0) {
            $model = InvestorAccountModel::findOrFail($account->getId());
            $model->update($data);
        } else {
            $model = InvestorAccountModel::create($data);
        }

        return $this->toDomainEntity($model);
    }

    public function findById(int $id): ?InvestorAccount
    {
        $model = InvestorAccountModel::find($id);
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByUserId(int $userId): ?InvestorAccount
    {
        $model = InvestorAccountModel::where('user_id', $userId)->first();
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByEmail(string $email): ?InvestorAccount
    {
        $model = InvestorAccountModel::where('email', $email)->first();
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByInvestmentRound(int $roundId): array
    {
        return InvestorAccountModel::where('investment_round_id', $roundId)
            ->get()
            ->map(fn($model) => $this->toDomainEntity($model))
            ->toArray();
    }

    public function findByStatus(InvestorStatus $status): array
    {
        return InvestorAccountModel::where('status', $status->value())
            ->get()
            ->map(fn($model) => $this->toDomainEntity($model))
            ->toArray();
    }

    public function all(): array
    {
        return InvestorAccountModel::all()
            ->map(fn($model) => $this->toDomainEntity($model))
            ->toArray();
    }

    public function getTotalInvestedAmount(): float
    {
        return (float) InvestorAccountModel::sum('investment_amount');
    }

    public function getInvestorCount(): int
    {
        return InvestorAccountModel::count();
    }

    public function delete(int $id): bool
    {
        return InvestorAccountModel::destroy($id) > 0;
    }

    private function toDomainEntity(InvestorAccountModel $model): InvestorAccount
    {
        return InvestorAccount::fromPersistence(
            id: $model->id,
            userId: $model->user_id,
            name: $model->name,
            email: $model->email,
            investmentAmount: (float) $model->investment_amount,
            investmentDate: new DateTimeImmutable($model->investment_date),
            investmentRoundId: $model->investment_round_id,
            status: InvestorStatus::fromString($model->status),
            equityPercentage: (float) $model->equity_percentage,
            createdAt: new DateTimeImmutable($model->created_at),
            updatedAt: new DateTimeImmutable($model->updated_at)
        );
    }
}
