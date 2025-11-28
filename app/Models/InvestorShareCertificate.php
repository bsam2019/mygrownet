<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestorShareCertificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'investor_account_id',
        'certificate_number',
        'shares_percentage',
        'investment_amount',
        'issue_date',
        'pdf_path',
        'verification_hash',
        'status',
        'notes',
    ];

    protected $casts = [
        'shares_percentage' => 'decimal:4',
        'investment_amount' => 'decimal:2',
        'issue_date' => 'date',
    ];

    public function investor(): BelongsTo
    {
        return $this->belongsTo(InvestorAccount::class, 'investor_account_id');
    }
}
