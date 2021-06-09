<?php

namespace LdapRecord\Models\OpenLDAP\Scopes;

use LdapRecord\Models\Model;
use LdapRecord\Models\Scope;
use LdapRecord\Query\Model\Builder;

class AddEntryUuidToSelects implements Scope
{
    /**
     * Add the entry UUID to the selected attributes.
     *
     * @param Builder $query
     * @param Model   $model
     *
     * @return void
     */
    public function apply(Builder $query, Model $model)
    {
        empty($query->columns)
            ? $query->addSelect(['*', $model->getGuidKey()])
            : $query->addSelect($model->getGuidKey());
    }
}
