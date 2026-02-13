<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpenseCategoryModel extends Model
{
    protected $table = 'cms_expense_categories';

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'requires_approval',
        'approval_limit',
        'is_active',
    ];

    protected $casts = [
        'requires_approval' => 'boolean',
        'is_active' => 'boolean',
        'approval_limit' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(ExpenseModel::class, 'category_id');
    }
}
