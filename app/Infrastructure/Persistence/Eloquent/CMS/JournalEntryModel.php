<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JournalEntryModel extends Model
{
    protected $table = 'cms_journal_entries';

    protected $fillable = [
        'company_id',
        'entry_number',
        'entry_date',
        'description',
        'reference',
        'is_posted',
        'created_by',
        'posted_at',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'is_posted' => 'boolean',
        'posted_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(CmsUserModel::class, 'created_by');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(JournalLineModel::class, 'journal_entry_id');
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopePosted($query)
    {
        return $query->where('is_posted', true);
    }

    public function isBalanced(): bool
    {
        $totalDebits = $this->lines->sum('debit_amount');
        $totalCredits = $this->lines->sum('credit_amount');
        
        return abs($totalDebits - $totalCredits) < 0.01;
    }
}
