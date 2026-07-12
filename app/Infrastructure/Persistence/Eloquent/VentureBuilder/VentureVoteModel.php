<?php

namespace App\Infrastructure\Persistence\Eloquent\VentureBuilder;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VentureVoteModel extends Model
{
    use HasFactory;

    protected $table = 'venture_votes';

    protected $fillable = [
        'resolution_id',
        'shareholder_id',
        'user_id',
        'vote',
        'equity_at_vote',
        'comment',
        'voted_at',
    ];

    protected $casts = [
        'equity_at_vote' => 'decimal:4',
        'voted_at' => 'datetime',
    ];

    public function resolution(): BelongsTo
    {
        return $this->belongsTo(VentureResolutionModel::class, 'resolution_id');
    }

    public function shareholder(): BelongsTo
    {
        return $this->belongsTo(VentureShareholderModel::class, 'shareholder_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
