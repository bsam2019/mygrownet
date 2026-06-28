<?php

namespace App\Infrastructure\BizDocs\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentTemplateModel extends Model
{
    protected $table = 'bizdocs_document_templates';

    protected $fillable = [
        'name',
        'document_type',
        'visibility',
        'owner_id',
        'industry_category',
        'layout_file',
        'template_structure',
        'thumbnail_path',
        'is_default',
    ];

    protected $casts = [
        'template_structure' => 'array',
        'is_default' => 'boolean',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(BusinessProfileModel::class, 'owner_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(DocumentModel::class, 'template_id');
    }
}
