<?php

namespace App\Domain\PlatformPayments\Entities;

enum TransactionStatus: string
{
    case Initiated = 'initiated';
    case Pending = 'pending';
    case Completed = 'completed';
    case Failed = 'failed';
    case Refunded = 'refunded';
    case PartiallyRefunded = 'partially_refunded';
    case Settled = 'settled';
    case Reconciled = 'reconciled';
}
