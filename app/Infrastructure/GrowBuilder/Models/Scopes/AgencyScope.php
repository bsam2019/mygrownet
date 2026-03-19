<?php

namespace App\Infrastructure\GrowBuilder\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class AgencyScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (auth()->check() && auth()->user()->currentAgency) {
            $builder->where($model->getTable() . '.agency_id', auth()->user()->currentAgency->id);
        }
    }
}
