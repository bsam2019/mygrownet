<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PPEDistributionModel extends Model
{
    protected $table = 'cms_ppe_distribution';

    protected $fillable = [
        'ppe_item_id',
        'user_id',
        'quantity',
        'distribution_date',
        'distributed_by',
        'condition',
        'return_date',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'distribution_date' => 'date',
        'return_date' => 'date',
    ];

    public function ppeItem(): BelongsTo
    {
        return $this->belongsTo(PPEItemModel::class, 'ppe_item_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function distributedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'distributed_by');
    }
}
