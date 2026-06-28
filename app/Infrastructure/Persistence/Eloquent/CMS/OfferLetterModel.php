<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfferLetterModel extends Model
{
    protected $table = 'cms_offer_letters';

    protected $fillable = [
        'application_id',
        'job_title',
        'salary',
        'start_date',
        'offer_letter_path',
        'sent_date',
        'response_deadline',
        'status',
        'terms',
    ];

    protected $casts = [
        'salary' => 'decimal:2',
        'start_date' => 'date',
        'sent_date' => 'date',
        'response_deadline' => 'date',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(JobApplicationModel::class, 'application_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'sent')
            ->where('response_deadline', '>=', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'sent')
            ->where('response_deadline', '<', now());
    }
}
