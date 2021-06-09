<?php

namespace LdapRecord\Models\Relations;

use LdapRecord\Models\Model;

class HasOne extends Relation
{
    /**
     * Get the results of the relationship.
     *
     * @return \LdapRecord\Query\Collection
     */
    public function getResults()
    {
        $model = $this->getForeignModelByValue(
            $this->getFirstAttributeValue($this->parent, $this->relationKey)
        );

        return $this->transformResults(
            $this->parent->newCollection($model ? [$model] : null)
        );
    }

    /**
     * Attach a model instance to the parent model.
     *
     * @param Model|string $model
     *
     * @return Model|string
     *
     * @throws \LdapRecord\LdapRecordException
     */
    public function attach($model)
    {
        $foreign = $model instanceof Model
            ? $this->getForeignValueFromModel($model)
            : $model;

        $this->parent->setAttribute($this->relationKey, $foreign)->save();

        return $model;
    }

    /**
     * Detach the related model from the parent.
     *
     * @return void
     *
     * @throws \LdapRecord\LdapRecordException
     */
    public function detach()
    {
        $this->parent->setAttribute($this->relationKey, null)->save();
    }
}
