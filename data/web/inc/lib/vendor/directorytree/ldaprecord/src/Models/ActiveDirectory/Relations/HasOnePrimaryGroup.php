<?php

namespace LdapRecord\Models\ActiveDirectory\Relations;

use LdapRecord\Models\Model;
use LdapRecord\Models\Relations\HasOne;

class HasOnePrimaryGroup extends HasOne
{
    /**
     * Get the foreign model by the given value.
     *
     * @param string $value
     *
     * @return Model|null
     */
    protected function getForeignModelByValue($value)
    {
        return $this->query->findBySid(
            $this->getParentModelObjectSid()
        );
    }

    /**
     * Get the foreign value from the given model.
     *
     * Retrieves the last RID from the models Object SID.
     *
     * @param Model $model
     *
     * @return string
     */
    protected function getForeignValueFromModel(Model $model)
    {
        $objectSidComponents = explode('-', $model->getConvertedSid());

        return end($objectSidComponents);
    }

    /**
     * Get the parent relationship models converted object sid.
     *
     * @return string
     */
    protected function getParentModelObjectSid()
    {
        return preg_replace(
            '/\d+$/',
            $this->parent->getFirstAttribute($this->relationKey),
            $this->parent->getConvertedSid()
        );
    }
}
