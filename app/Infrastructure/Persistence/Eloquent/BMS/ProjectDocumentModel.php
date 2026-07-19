<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectDocumentModel extends Model
{
    protected $table = 'cms_project_documents';

    protected $fillable = [
        'project_id',
        'uploaded_by',
        'document_type',
        'title',
        'description',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'version',
        'status',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'version' => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(ProjectModel::class, 'project_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(EmployeeModel::class, 'uploaded_by');
    }

    public function getDownloadUrlAttribute(): string
    {
        return \Storage::disk('s3')->url($this->file_path);
    }
}
