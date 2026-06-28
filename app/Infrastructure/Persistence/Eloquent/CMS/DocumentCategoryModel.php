<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentCategoryModel extends Model
{
    use SoftDeletes;

    protected $table = 'cms_document_categories';

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'parent_id',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(DocumentCategoryModel::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(DocumentCategoryModel::class, 'parent_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(DocumentModel::class, 'category_id');
    }
}
