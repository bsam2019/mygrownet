<?php

namespace App\Infrastructure\Persistence\Eloquent\Workshop;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use App\Infrastructure\Persistence\Eloquent\Payment\MemberPaymentModel;

class WorkshopRegistrationModel extends Model
{
    protected $table = 'workshop_registrations';

    protected $fillable = [
        'workshop_id',
        'user_id',
        'payment_id',
        'status',
        'registration_notes',
        'attended_at',
        'completed_at',
        'certificate_issued',
        'certificate_issued_at',
        'points_awarded',
        'points_awarded_at',
    ];

    protected $casts = [
        'attended_at' => 'datetime',
        'completed_at' => 'datetime',
        'certificate_issued' => 'boolean',
        'certificate_issued_at' => 'datetime',
        'points_awarded' => 'boolean',
        'points_awarded_at' => 'datetime',
    ];

    public function workshop(): BelongsTo
    {
        return $this->belongsTo(WorkshopModel::class, 'workshop_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(MemberPaymentModel::class, 'payment_id');
    }
}
