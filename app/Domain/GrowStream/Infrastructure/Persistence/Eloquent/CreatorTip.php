<?php

namespace App\Domain\GrowStream\Infrastructure\Persistence\Eloquent;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreatorTip extends Model
{
    use HasFactory;

    protected $table = 'growstream_creator_tips';

    protected $fillable = [
        'user_id',
        'creator_id',
        'amount',
        'currency',
        'message',
        'is_anonymous',
        'provider_reference',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_anonymous' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(CreatorProfile::class, 'creator_id');
    }
}
