<?php

namespace LdapRecord\Models\ActiveDirectory\Concerns;

use LdapRecord\Models\ActiveDirectory\Relations\HasOnePrimaryGroup;

trait HasPrimaryGroup
{
    /**
     * Returns a new has one primary group relationship.
     *
     * @param mixed  $related
     * @param string $relationKey
     * @param string $foreignKey
     *
     * @return HasOnePrimaryGroup
     */
    public function hasOnePrimaryGroup($related, $relationKey, $foreignKey = 'primarygroupid')
    {
        return new HasOnePrimaryGroup($this->newQuery(), $this, $related, $relationKey, $foreignKey);
    }
}
