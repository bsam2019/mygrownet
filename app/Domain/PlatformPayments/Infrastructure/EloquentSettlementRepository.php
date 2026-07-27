<?php

namespace App\Domain\PlatformPayments\Infrastructure;

use App\Domain\PlatformPayments\Entities\Settlement;
use App\Domain\PlatformPayments\Repositories\SettlementRepositoryInterface;

class EloquentSettlementRepository implements SettlementRepositoryInterface
{
    public function findById(int $id): ?Settlement
    {
        $model = SettlementModel::find($id);
        return $model ? $this->toDomain($model) : null;
    }

    public function findByOrganization(int $organizationId): array
    {
        return SettlementModel::where('organization_id', $organizationId)
            ->orderBy('settlement_date', 'desc')
            ->get()
            ->map(fn(SettlementModel $m) => $this->toDomain($m))
            ->all();
    }

    public function findByProvider(string $provider, \DateTimeImmutable $from, \DateTimeImmutable $to): array
    {
        return SettlementModel::where('provider', $provider)
            ->whereBetween('settlement_date', [$from->format('Y-m-d'), $to->format('Y-m-d')])
            ->get()
            ->map(fn(SettlementModel $m) => $this->toDomain($m))
            ->all();
    }

    public function findUnreconciled(): array
    {
        return SettlementModel::whereNotIn('status', ['reconciled'])
            ->orderBy('settlement_date', 'asc')
            ->get()
            ->map(fn(SettlementModel $m) => $this->toDomain($m))
            ->all();
    }

    public function save(Settlement $settlement): Settlement
    {
        $data = $settlement->toArray();

        $model = $settlement->id()
            ? SettlementModel::findOrFail($settlement->id())
            : new SettlementModel();

        $model->organization_id = $data['organization_id'];
        $model->provider = $data['provider'];
        $model->provider_settlement_id = $data['provider_settlement_id'];
        $model->expected_amount = $data['expected_amount'];
        $model->actual_amount = $data['actual_amount'];
        $model->fee_amount = $data['fee_amount'];
        $model->currency = $data['currency'];
        $model->status = $settlement->status();
        $model->settlement_date = $data['settlement_date'] ?? null;
        $model->reconciled_at = $data['reconciled_at'] ?? null;
        $model->discrepancy_notes = $data['discrepancy_notes'] ?? null;
        $model->save();

        return $this->toDomain($model);
    }

    private function toDomain(SettlementModel $model): Settlement
    {
        return Settlement::reconstitute(
            id: $model->id,
            organizationId: $model->organization_id,
            provider: $model->provider,
            providerSettlementId: $model->provider_settlement_id,
            expectedAmount: (float) $model->expected_amount,
            actualAmount: (float) $model->actual_amount,
            feeAmount: (float) $model->fee_amount,
            currency: $model->currency,
            status: $model->status,
            settlementDate: $model->settlement_date ? new \DateTimeImmutable($model->settlement_date) : null,
            reconciledAt: $model->reconciled_at ? new \DateTimeImmutable($model->reconciled_at) : null,
            discrepancyNotes: $model->discrepancy_notes,
            createdAt: new \DateTimeImmutable($model->created_at),
            updatedAt: $model->updated_at ? new \DateTimeImmutable($model->updated_at) : null,
        );
    }
}
