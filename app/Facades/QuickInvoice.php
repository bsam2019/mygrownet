<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Quick Invoice Facade for easy access throughout the platform
 * 
 * @method static array createDocument(\App\Models\User $user, array $data)
 * @method static array getUserSubscriptionStatus(\App\Models\User $user)
 * @method static array getAvailableTemplates(\App\Models\User $user)
 * @method static array getUserDocuments(\App\Models\User $user, int $limit = 10)
 * @method static array|null getUserProfile(\App\Models\User $user)
 * @method static bool canUserAccessTemplate(\App\Models\User $user, string $templateId)
 */
class QuickInvoice extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \App\Services\QuickInvoice\QuickInvoiceService::class;
    }
}