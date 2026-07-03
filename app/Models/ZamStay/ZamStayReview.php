<?php

namespace App\Models\ZamStay;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ZamStayReview extends Model
{
    protected $table = 'zamstay_reviews';

    protected $fillable = [
        'booking_id',
        'user_id',
        'property_id',
        'rating',
        'comment',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(ZamStayBooking::class, 'booking_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(ZamStayProperty::class, 'property_id');
    }
}
