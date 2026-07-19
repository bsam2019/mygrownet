<?php

namespace App\Http\Controllers\BMS\Concerns;

use Illuminate\Http\Request;

trait HasBmsAccess
{
    protected function getBmsUserOrFail(Request $request)
    {
        $bmsUser = $request->user()?->cmsUser;
        abort_unless($bmsUser, 403, 'No BMS company access.');

        return $bmsUser;
    }

    protected function getCompanyId(Request $request): int
    {
        return $this->getBmsUserOrFail($request)->company_id;
    }
}
