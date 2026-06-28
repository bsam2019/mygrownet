<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnboardingTaskModel extends Model
{
    protected $table = 'cms_onboarding_tasks';

    protected $fillable = [
        'template_id',
        'task_name',
        'description',
        'task_category',
        'assigned_to_role',
        'due_days_after_start',
        'is_mandatory',
        'display_order',
    ];

    protected $casts = [
        'due_days_after_start' => 'integer',
        'is_mandatory' => 'boolean',
        'display_order' => 'integer',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(OnboardingTemplateModel::class, 'template_id');
    }
}
