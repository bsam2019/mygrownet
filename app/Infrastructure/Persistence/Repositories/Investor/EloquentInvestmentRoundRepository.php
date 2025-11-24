<?php

namespace App\Infrastructure\Persistence\Repositories\Investor;

use App\Domain\Investor\Entities\InvestmentRound;
use App\Domain\Investor\Repositories\InvestmentRoundRepositoryInterface;
use App\Domain\Investor\ValueObjects\InvestmentRoundStatus;
use App\Infrastructure\Persistence\Eloquent\Investor\InvestmentRoundModel;
use DateTimeImmutable;

/**
 * Eloquent Investment Round Repository
 */
class EloquentInvestmentRoundRepository implements InvestmentRoundRepositoryInterface
{
    public function save(InvestmentRound $round): InvestmentRound
    {
        $model = $round->getId() > 0
            ? InvestmentRoundModel::findOrFail($round->getId())
            : new InvestmentRoundModel();

        $model->fill([
            'name' => $round->getName(),
            'description' => $round->getDescription(),
            'goal_amount' => $round->getGoalAmount(),
            'raised_amount' => $round->getRaisedAmount(),
            'minimum_investment' => $round->getMinimumInvestment(),
            'valuation' => $round->getValuation(),
            'equity_percentage' => $round->getEquityPercentage(),
            'expected_roi' => $round->getExpectedRoi(),
            'use_of_funds' => $round->getUseOfFunds(),
            'status' => $round->getStatus()->value(),
            'start_date' => $round->getStartDate()?->format('Y-m-d'),
            'end_date' => $round->getEndDate()?->format('Y-m-d'),
            'is_featured' => $round->isFeatured(),
        ]);

        $model->save();

        return $this->toDomainEntity($model);
    }

    public function findById(int $id): ?InvestmentRound
    {
        $model = InvestmentRoundModel::find($id);
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function getFeaturedRound(): ?InvestmentRound
    {
        $model = InvestmentRoundModel::where('is_featured', true)
            ->where('status', 'active')
            ->first();

        return $model ? $this->toDomainEntity($model) : null;
    }

    public function getActiveRounds(): array
    {
        $models = InvestmentRoundModel::where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->all();
    }

    public function findActive(): array
    {
        return $this->getActiveRounds();
    }

    public function all(): array
    {
        return $this->getAll();
    }

    public function getAll(): array
    {
        $models = InvestmentRoundModel::orderBy('created_at', 'desc')->get();
        return $models->map(fn($model) => $this->toDomainEntity($model))->all();
    }

    public function delete(int $id): void
    {
        InvestmentRoundModel::destroy($id);
    }

    private function toDomainEntity(InvestmentRoundModel $model): InvestmentRound
    {
        return InvestmentRound::fromPersistence(
            id: $model->id,
            name: $model->name,
            description: $model->description,
            goalAmount: $model->goal_amount,
            raisedAmount: $model->raised_amount,
            minimumInvestment: $model->minimum_investment,
            valuation: $model->valuation,
            equityPercentage: $model->equity_percentage,
            expectedRoi: $model->expected_roi,
            useOfFunds: $model->use_of_funds ?? [],
            status: InvestmentRoundStatus::from($model->status),
            startDate: $model->start_date ? DateTimeImmutable::createFromMutable($model->start_date) : null,
            endDate: $model->end_date ? DateTimeImmutable::createFromMutable($model->end_date) : null,
            isFeatured: $model->is_featured,
            createdAt: DateTimeImmutable::createFromMutable($model->created_at),
            updatedAt: DateTimeImmutable::createFromMutable($model->updated_at)
        );
    }
}
