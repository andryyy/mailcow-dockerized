<?php

namespace LdapRecord\Models\Relations;

use LdapRecord\Query\Collection;

class HasManyIn extends OneToMany
{
    /**
     * Get the relationships results.
     *
     * @return Collection
     */
    public function getRelationResults()
    {
        $results = $this->parent->newCollection();

        foreach ((array) $this->parent->getAttribute($this->relationKey) as $value) {
            if ($foreign = $this->getForeignModelByValue($value)) {
                $results->push($foreign);
            }
        }

        return $this->transformResults($results);
    }
}
