<?php

namespace App\Infrastructure\Persistence\Eloquent\Investor;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestorDocumentModel extends Model
{
    protected $table = 'investor_documents';

    protected $fillable = [
        'title',
        'description',
        'category',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'upload_date',
        'uploaded_by',
        'visible_to_all',
        'investment_round_id',
        'status',
        'download_count',
    ];

    protected $casts = [
        'upload_date' => 'datetime',
        'visible_to_all' => 'boolean',
        'file_size' => 'integer',
        'download_count' => 'integer',
    ];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'uploaded_by');
    }

    public function investmentRound(): BelongsTo
    {
        return $this->belongsTo(InvestmentRoundModel::class, 'investment_round_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeVisibleToAll($query)
    {
        return $query->where('visible_to_all', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeForInvestmentRound($query, int $roundId)
    {
        return $query->where(function ($q) use ($roundId) {
            $q->where('visible_to_all', true)
              ->orWhere('investment_round_id', $roundId);
        });
    }
}