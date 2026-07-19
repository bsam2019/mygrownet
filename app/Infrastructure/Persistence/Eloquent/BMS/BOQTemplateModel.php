<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BOQTemplateModel extends Model
{
    protected $table = 'cms_boq_templates';

    protected $fillable = [
        'company_id', 'name', 'code', 'description', 'project_type', 'status',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }
}
