<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerDocumentModel extends Model
{
    protected $table = 'cms_customer_documents';

    protected $fillable = [
        'customer_id',
        'document_type',
        'file_path',
        'file_name',
        'file_size',
        'uploaded_by',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    protected $appends = ['download_url'];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(CustomerModel::class, 'customer_id');
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

    /**
     * Generate presigned download URL for S3 files
     * Valid for 1 hour
     */
    public function getDownloadUrlAttribute(): string
    {
        // If file_path starts with /storage/, it's an old local file
        if (str_starts_with($this->file_path, '/storage/')) {
            return $this->file_path;
        }

        // Otherwise, it's an S3 key - generate presigned URL
        try {
            return \Illuminate\Support\Facades\Storage::disk('s3')->temporaryUrl(
                $this->file_path,
                now()->addHour()
            );
        } catch (\Exception $e) {
            \Log::error('Failed to generate presigned URL for customer document', [
                'document_id' => $this->id,
                's3_key' => $this->file_path,
                'error' => $e->getMessage(),
            ]);
            return '#';
        }
    }
}
