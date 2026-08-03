<?php

namespace App\Infrastructure\Persistence\Eloquent\GrowFinance;

use Illuminate\Database\Eloquent\Model;

class GrowFinanceProfileModel extends Model
{
    protected $table = "growfinance_profiles";
    protected $fillable = ["organization_id", "user_id", "business_name", "account_number"];
}
