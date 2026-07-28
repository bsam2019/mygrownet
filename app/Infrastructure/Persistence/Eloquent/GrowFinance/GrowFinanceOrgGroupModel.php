<?php

namespace App\Infrastructure\Persistence\Eloquent\GrowFinance;

use Illuminate\Database\Eloquent\Model;

class GrowFinanceOrgGroupModel extends Model
{
    protected $table = 'growfinance_org_groups';

    protected $fillable = [
        'parent_org_id',
        'child_org_id',
        'relationship_type',
        'consolidation_settings',
        'is_active',
    ];

    protected $casts = [
        'consolidation_settings' => 'array',
        'is_active' => 'boolean',
    ];
}
