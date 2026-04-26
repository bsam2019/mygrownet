<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DefectPhotoModel extends Model
{
    protected $table = 'cms_defect_photos';

    protected $fillable = [
        'defect_id',
        'file_path',
        'caption',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function defect(): BelongsTo
    {
        return $this->belongsTo(DefectModel::class, 'defect_id');
    }
}
