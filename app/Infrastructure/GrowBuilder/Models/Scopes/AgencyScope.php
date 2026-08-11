<?php

namespace App\Infrastructure\GrowBuilder\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class AgencyScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * Note: if the authenticated user has no currentAgency (e.g. platform super-admins
     * browsing via /growbuilder/clients directly), the scope is intentionally skipped
     * so they see all clients across all agencies. Each agency-owning user will still
     * get filtered to their own agency.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (auth()->check() && auth()->user()->currentAgency) {
            $builder->where($model->getTable() . '.agency_id', auth()->user()->currentAgency->id);
        }
        // else: no scope applied — super-admins / users without an agency see all records
    }
}
