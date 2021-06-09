<?php

namespace LdapRecord\Models;

use LdapRecord\Query\Model\Builder;

interface Scope
{
    /**
     * Apply the scope to the given query.
     *
     * @param Builder $query
     * @param Model   $model
     *
     * @return void
     */
    public function apply(Builder $query, Model $model);
}
