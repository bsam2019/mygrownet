<?php

namespace App\Infrastructure\Persistence\Eloquent\BMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContractTemplateModel extends Model
{
    protected $table = 'cms_contract_templates';

    protected $fillable = [
        'company_id', 'name', 'description', 'content', 'category', 'is_active', 'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(CmsUserModel::class, 'created_by');
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(ContractModel::class, 'template_id');
    }
}
