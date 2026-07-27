<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Infrastructure;

use App\Domain\Core\Contracts\DimensionProvider;
use App\Domain\GrowFinance\Repositories\AccountRepositoryInterface;

class GrowFinanceDimensionProvider implements DimensionProvider
{
    public function __construct(
        private AccountRepositoryInterface $accounts,
    ) {}

    public function capability(): string
    {
        return 'growfinance_dimensions';
    }

    public function getDimensions(): array
    {
        return [
            'cost_centre' => 'Cost Centre',
            'account_category' => 'Account Category',
        ];
    }

    public function resolveLabels(array $dimensionIds): array
    {
        $labels = [];

        if (!empty($dimensionIds['cost_centre'] ?? [])) {
            $labels['cost_centre'] = [];
        }

        if (!empty($dimensionIds['account_category'] ?? [])) {
            $categories = $this->accounts->findActive(0);
            $seen = [];
            foreach ($categories as $account) {
                if ($account->category && !isset($seen[$account->category])) {
                    $seen[$account->category] = true;
                }
            }
            $labels['account_category'] = $seen ?? [];
        }

        return $labels;
    }
}
