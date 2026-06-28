<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BgfContract extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'user_id',
        'contract_number',
        'funding_amount',
        'member_contribution',
        'member_profit_percentage',
        'mygrownet_profit_percentage',
        'start_date',
        'end_date',
        'terms',
        'milestones',
        'disbursement_schedule',
        'special_conditions',
        'contract_content',
        'contract_pdf_url',
        'member_signature',
        'member_signed_at',
        'member_ip_address',
        'mygrownet_signatory_id',
        'mygrownet_signature',
        'mygrownet_signed_at',
        'status',
        'termination_reason',
        'terminated_at',
    ];

    protected $casts = [
        'funding_amount' => 'decimal:2',
        'member_contribution' => 'decimal:2',
        'terms' => 'array',
        'milestones' => 'array',
        'disbursement_schedule' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'member_signed_at' => 'datetime',
        'mygrownet_signed_at' => 'datetime',
        'terminated_at' => 'datetime',
    ];

    // Relationships
    public function project(): BelongsTo
    {
        return $this->belongsTo(BgfProject::class, 'project_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function mygrownetSignatory(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mygrownet_signatory_id');
    }

    // Helper Methods
    public function generateContractNumber(): string
    {
        return 'BGF-CON-' . date('Y') . '-' . str_pad($this->id ?? rand(1000, 9999), 4, '0', STR_PAD_LEFT);
    }

    public function isFullySigned(): bool
    {
        return $this->member_signed_at !== null && $this->mygrownet_signed_at !== null;
    }

    public function isPendingMemberSignature(): bool
    {
        return $this->member_signed_at === null && $this->status === 'pending_member_signature';
    }

    public function isPendingMygrownetSignature(): bool
    {
        return $this->member_signed_at !== null && 
               $this->mygrownet_signed_at === null && 
               $this->status === 'pending_mygrownet_signature';
    }
}
