<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\GrowFinance;

use Illuminate\Database\Eloquent\Model;

class GrowFinanceScenarioModel extends Model
{
    protected $table = 'growfinance_scenarios';

    protected $fillable = [
        'business_id',
        'name',
        'parameters',
        'results',
    ];

    protected $casts = [
        'parameters' => 'array',
        'results' => 'array',
    ];

    public function scopeForBusiness($query, int $businessId)
    {
        return $query->where('business_id', $businessId);
    }
}
