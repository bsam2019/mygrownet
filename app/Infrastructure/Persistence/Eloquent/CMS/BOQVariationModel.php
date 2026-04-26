<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BOQVariationModel extends Model
{
    protected $table = 'cms_boq_variations';

    protected $fillable = [
        'boq_id', 'variation_number', 'description', 'type', 'amount',
        'date_raised', 'status', 'approved_by', 'approved_date', 'approval_notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date_raised' => 'date',
        'approved_date' => 'date',
    ];

    public function boq(): BelongsTo
    {
        return $this->belongsTo(BOQModel::class, 'boq_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(EmployeeModel::class, 'approved_by');
    }
}
