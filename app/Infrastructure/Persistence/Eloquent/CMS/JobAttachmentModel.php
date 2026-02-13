<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobAttachmentModel extends Model
{
    protected $table = 'cms_job_attachments';

    protected $fillable = [
        'job_id',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'uploaded_by',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    public function job(): BelongsTo
    {
        return $this->belongsTo(JobModel::class, 'job_id');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(CmsUserModel::class, 'uploaded_by');
    }

    public function getFileSizeHumanAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' bytes';
    }
}
