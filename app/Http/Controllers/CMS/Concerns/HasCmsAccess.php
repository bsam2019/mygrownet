<?php

namespace App\Http\Controllers\CMS\Concerns;

use Illuminate\Http\Request;

trait HasCmsAccess
{
    protected function getCmsUserOrFail(Request $request)
    {
        $cmsUser = $request->user()?->cmsUser;
        abort_unless($cmsUser, 403, 'No CMS company access.');

        return $cmsUser;
    }

    protected function getCompanyId(Request $request): int
    {
        return $this->getCmsUserOrFail($request)->company_id;
    }
}
