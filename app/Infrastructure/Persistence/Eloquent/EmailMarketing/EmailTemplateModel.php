<?php

namespace App\Infrastructure\Persistence\Eloquent\EmailMarketing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailTemplateModel extends Model
{
    protected $table = 'email_templates';

    protected $fillable = [
        'name',
        'subject',
        'preview_text',
        'html_content',
        'text_content',
        'variables',
        'category',
        'is_system',
        'created_by',
    ];

    protected $casts = [
        'variables' => 'array',
        'is_system' => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }
}
