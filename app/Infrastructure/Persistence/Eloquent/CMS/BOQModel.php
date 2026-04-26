<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BOQModel extends Model
{
    protected $table = 'cms_boqs';

    protected $fillable = [
        'company_id', 'project_id', 'quotation_id', 'template_id', 'boq_number',
        'title', 'description', 'version', 'total_amount', 'contingency_percentage',
        'contingency_amount', 'grand_total', 'status', 'prepared_date', 'prepared_by',
    ];

    protected $casts = [
        'version' => 'integer',
        'total_amount' => 'decimal:2',
        'contingency_percentage' => 'decimal:2',
        'contingency_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'prepared_date' => 'date',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(ProjectModel::class, 'project_id');
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(QuotationModel::class, 'quotation_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(BOQTemplateModel::class, 'template_id');
    }

    public function preparer(): BelongsTo
    {
        return $this->belongsTo(EmployeeModel::class, 'prepared_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(BOQItemModel::class, 'boq_id');
    }

    public function variations(): HasMany
    {
        return $this->hasMany(BOQVariationModel::class, 'boq_id');
    }

    public function recalculateTotals(): void
    {
        $this->total_amount = $this->items()->sum('amount');
        $this->contingency_amount = $this->total_amount * ($this->contingency_percentage / 100);
        $this->grand_total = $this->total_amount + $this->contingency_amount;
        $this->save();
    }
}
