<?php

namespace App\Infrastructure\Persistence\Eloquent\StockFlow;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SaCommentModel extends Model
{
    protected $table = 'sa_comments';
    protected $fillable = [
        'sa_company_id', 'commentable_type', 'commentable_id',
        'sa_user_id', 'body',
    ];

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(SaUserModel::class, 'sa_user_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(SaCompanyModel::class, 'sa_company_id');
    }
}
