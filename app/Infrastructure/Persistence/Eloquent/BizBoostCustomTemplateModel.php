<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BizBoostCustomTemplateModel extends Model
{
    use HasFactory;

    protected $table = 'bizboost_custom_templates';

    protected $fillable = [
        'business_id',
        'base_template_id',
        'name',
        'description',
        'category',
        'template_data',
        'thumbnail_path',
        'width',
        'height',
    ];

    protected $casts = ['template_data' => 'array'];

    public function business(): BelongsTo
    {
        return $this->belongsTo(BizBoostBusinessModel::class, 'business_id');
    }

    public function baseTemplate(): BelongsTo
    {
        return $this->belongsTo(BizBoostTemplateModel::class, 'base_template_id');
    }
}
