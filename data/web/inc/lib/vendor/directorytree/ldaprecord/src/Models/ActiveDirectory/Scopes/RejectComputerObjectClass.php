<?php

namespace LdapRecord\Models\ActiveDirectory\Scopes;

use LdapRecord\Models\Model;
use LdapRecord\Models\Scope;
use LdapRecord\Query\Model\Builder;

class RejectComputerObjectClass implements Scope
{
    /**
     * Prevent computer objects from being included in results.
     *
     * @param Builder $query
     * @param Model   $model
     *
     * @return void
     */
    public function apply(Builder $query, Model $model)
    {
        $query->where('objectclass', '!=', 'computer');
    }
}
