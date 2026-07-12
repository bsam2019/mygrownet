<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Models\Transaction;
use Illuminate\Support\Collection;

class EloquentTransactionRepository
{
    public function findByUser(int $userId): Collection
    {
        return Transaction::where("user_id", $userId)->get();
    }
}