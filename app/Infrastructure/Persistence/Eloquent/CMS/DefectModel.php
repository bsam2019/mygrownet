<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DefectModel extends Model
{
    protected $table = 'cms_defects';

    protected $fillable = [
        'company_id',
        'job_id',
        'site_visit_id',
        'defect_number',
        'title',
        'description',
        'severity',
        'status',
        'identified_date',
        'target_resolution_date',
        'actual_resolution_date',
        'identified_by',
        'assigned_to',
        'resolution_notes',
        'resolved_by',
    ];

    protected $casts = [
        'identified_date' => 'date',
        'target_resolution_date' => 'date',
        'actual_resolution_date' => 'date',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(JobModel::class, 'job_id');
    }

    public function siteVisit(): BelongsTo
    {
        return $this->belongsTo(SiteVisitModel::class, 'site_visit_id');
    }

    public function identifiedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'identified_by');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'assigned_to');
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'resolved_by');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(DefectPhotoModel::class, 'defect_id');
    }
}

class InstallationPhotoModel extends Model
{
    protected $table = 'cms_installation_photos';
    protected $fillable = ['site_visit_id', 'photo_type', 'file_path', 'file_name', 'mime_type', 'file_size', 'caption', 'sort_order'];
}

class DefectPhotoModel extends Model
{
    protected $table = 'cms_defect_photos';
    protected $fillable = ['defect_id', 'file_path', 'file_name', 'caption'];
}

class CustomerSignoffModel extends Model
{
    protected $table = 'cms_customer_signoffs';
    protected $fillable = ['site_visit_id', 'customer_name', 'customer_email', 'customer_phone', 'signature_data', 'signed_at', 'comments', 'satisfaction_rating', 'feedback'];
    protected $casts = ['signed_at' => 'datetime'];
}

class InstallationChecklistModel extends Model
{
    protected $table = 'cms_installation_checklists';
    protected $fillable = ['company_id', 'checklist_name', 'description', 'checklist_type', 'is_template', 'is_active'];
    protected $casts = ['is_template' => 'boolean', 'is_active' => 'boolean'];
    
    public function items(): HasMany
    {
        return $this->hasMany(ChecklistItemModel::class, 'checklist_id');
    }
}

class ChecklistItemModel extends Model
{
    protected $table = 'cms_checklist_items';
    protected $fillable = ['checklist_id', 'item_text', 'description', 'is_required', 'sort_order'];
    protected $casts = ['is_required' => 'boolean'];
}

class InstallationChecklistResponseModel extends Model
{
    protected $table = 'cms_installation_checklist_responses';
    protected $fillable = ['site_visit_id', 'checklist_id', 'checklist_item_id', 'status', 'notes', 'checked_by', 'checked_at'];
    protected $casts = ['checked_at' => 'datetime'];
}

class InstallationTeamMemberModel extends Model
{
    protected $table = 'cms_installation_team_members';
    protected $fillable = ['installation_schedule_id', 'user_id', 'role'];
}
