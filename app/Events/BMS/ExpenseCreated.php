<?php

namespace App\Events\BMS;

use App\Infrastructure\Persistence\Eloquent\CMS\ExpenseModel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ExpenseCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public ExpenseModel $expense
    ) {}
}
