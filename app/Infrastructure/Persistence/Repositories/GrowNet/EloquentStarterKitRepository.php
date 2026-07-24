<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\GrowNet;

use App\Domain\GrowNet\Entities\StarterKit;
use App\Domain\GrowNet\Repositories\StarterKitRepositoryInterface;
use App\Domain\GrowNet\ValueObjects\MemberId;
use App\Domain\GrowNet\ValueObjects\Money;
use App\Infrastructure\Persistence\Eloquent\GrowNet\StarterKitPurchase;
use DateTimeImmutable;

class EloquentStarterKitRepository implements StarterKitRepositoryInterface
{
    public function findByMemberId(MemberId $memberId): ?StarterKit
    {
        $model = StarterKitPurchase::where('user_id', $memberId->value())
            ->latest()
            ->first();
        return $model ? $this->toDomain($model) : null;
    }

    public function save(StarterKit $starterKit): StarterKit
    {
        $data = [
            'user_id' => $starterKit->memberId()->value(),
            'tier' => $starterKit->tier(),
            'status' => $starterKit->status(),
            'price' => $starterKit->price()->amount(),
            'purchased_at' => $starterKit->purchasedAt()?->format('Y-m-d H:i:s'),
        ];

        if ($starterKit->id() > 0) {
            StarterKitPurchase::where('id', $starterKit->id())->update($data);
            return $starterKit;
        }

        $model = StarterKitPurchase::create($data);
        return $this->toDomain($model);
    }

    private function toDomain($model): StarterKit
    {
        return new StarterKit(
            id: $model->id,
            memberId: new MemberId($model->user_id),
            tier: $model->tier ?? 'associate',
            status: $model->status ?? 'pending',
            price: new Money((float) ($model->price ?? 0)),
            purchasedAt: $model->purchased_at ? new DateTimeImmutable($model->purchased_at) : ($model->created_at ? new DateTimeImmutable($model->created_at->format('Y-m-d H:i:s')) : null),
        );
    }
}
