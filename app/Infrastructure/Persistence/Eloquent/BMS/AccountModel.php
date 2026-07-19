<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use App\Domain\CMS\Core\ValueObjects\AccountType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccountModel extends Model
{
    protected $table = 'cms_accounts';

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'type',
        'category',
        'description',
        'current_balance',
        'is_system',
        'is_active',
    ];

    protected $casts = [
        'current_balance' => 'decimal:2',
        'is_system' => 'boolean',
        'is_active' => 'boolean',
        'type' => AccountType::class,
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function journalLines(): HasMany
    {
        return $this->hasMany(JournalLineModel::class, 'account_id');
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
