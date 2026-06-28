<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PasswordHistoryModel extends Model
{
    protected $table = 'cms_password_history';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'password',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(CmsUserModel::class, 'user_id');
    }
}
