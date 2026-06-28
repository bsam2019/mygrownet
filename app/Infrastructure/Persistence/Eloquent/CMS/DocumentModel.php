<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentModel extends Model
{
    use SoftDeletes;

    protected $table = 'cms_documents';

    protected $fillable = [
        'company_id',
        'category_id',
        'title',
        'document_number',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'version',
        'description',
        'tags',
        'uploaded_by',
        'expiry_date',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'version' => 'integer',
        'expiry_date' => 'date',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(DocumentCategoryModel::class, 'category_id');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(DocumentVersionModel::class, 'document_id');
    }

    public function shares(): HasMany
    {
        return $this->hasMany(DocumentShareModel::class, 'document_id');
    }

    public function signatures(): HasMany
    {
        return $this->hasMany(DocumentSignatureModel::class, 'document_id');
    }

    public function accessLogs(): HasMany
    {
        return $this->hasMany(DocumentAccessLogModel::class, 'document_id');
    }
}
