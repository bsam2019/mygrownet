<?php

namespace App\Domain\FinancialServicesCore\Events;

use App\Domain\Core\Events\PlatformEvent;

class FxRateUpdated extends PlatformEvent
{
    public const NAME = 'platform.fx.rate_updated.v1';

    public function __construct(
        public readonly string $fromCurrency,
        public readonly string $toCurrency,
        public readonly float $rate,
        public readonly string $source,
        public readonly \DateTimeImmutable $date,
    ) {
        parent::__construct(
            entityId: "{$fromCurrency}→{$toCurrency}",
            eventName: self::NAME,
        );
    }

    public function toPayload(): array
    {
        return [
            'from_currency' => $this->fromCurrency,
            'to_currency' => $this->toCurrency,
            'rate' => $this->rate,
            'source' => $this->source,
            'date' => $this->date->format('Y-m-d'),
        ];
    }
}
