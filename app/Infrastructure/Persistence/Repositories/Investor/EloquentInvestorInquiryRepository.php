<?php

namespace App\Infrastructure\Persistence\Repositories\Investor;

use App\Domain\Investor\Entities\InvestorInquiry;
use App\Domain\Investor\Repositories\InvestorInquiryRepositoryInterface;
use App\Domain\Investor\ValueObjects\InvestmentRange;
use App\Domain\Investor\ValueObjects\InquiryStatus;
use App\Infrastructure\Persistence\Eloquent\Investor\InvestorInquiryModel;
use DateTimeImmutable;

/**
 * Eloquent Investor Inquiry Repository
 * 
 * Infrastructure implementation of the investor inquiry repository
 */
class EloquentInvestorInquiryRepository implements InvestorInquiryRepositoryInterface
{
    public function save(InvestorInquiry $inquiry): InvestorInquiry
    {
        $model = $inquiry->getId() > 0
            ? InvestorInquiryModel::findOrFail($inquiry->getId())
            : new InvestorInquiryModel();

        $model->fill([
            'name' => $inquiry->getName(),
            'email' => $inquiry->getEmail(),
            'phone' => $inquiry->getPhone(),
            'investment_range' => $inquiry->getInvestmentRange()->value(),
            'message' => $inquiry->getMessage(),
            'status' => $inquiry->getStatus()->value(),
        ]);

        $model->save();

        return $this->toDomainEntity($model);
    }

    public function findById(int $id): ?InvestorInquiry
    {
        $model = InvestorInquiryModel::find($id);

        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByStatus(InquiryStatus $status): array
    {
        $models = InvestorInquiryModel::where('status', $status->value())
            ->orderBy('created_at', 'desc')
            ->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->all();
    }

    public function findHighValueInquiries(): array
    {
        $models = InvestorInquiryModel::whereIn('investment_range', ['100-250', '250+'])
            ->orderBy('created_at', 'desc')
            ->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->all();
    }

    public function getAll(int $page = 1, int $perPage = 20): array
    {
        $models = InvestorInquiryModel::orderBy('created_at', 'desc')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->all();
    }

    public function countByStatus(InquiryStatus $status): int
    {
        return InvestorInquiryModel::where('status', $status->value())->count();
    }

    /**
     * Convert Eloquent model to domain entity
     */
    private function toDomainEntity(InvestorInquiryModel $model): InvestorInquiry
    {
        return InvestorInquiry::fromPersistence(
            id: $model->id,
            name: $model->name,
            email: $model->email,
            phone: $model->phone,
            investmentRange: InvestmentRange::from($model->investment_range),
            message: $model->message,
            status: InquiryStatus::from($model->status),
            createdAt: DateTimeImmutable::createFromMutable($model->created_at),
            updatedAt: DateTimeImmutable::createFromMutable($model->updated_at)
        );
    }
}
