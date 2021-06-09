<?php

namespace Adldap\Models\Concerns;

trait HasCriticalSystemObject
{
    /**
     * Returns true / false if the entry is a critical system object.
     *
     * @return null|bool
     */
    public function isCriticalSystemObject()
    {
        $attribute = $this->getFirstAttribute($this->schema->isCriticalSystemObject());

        return $this->convertStringToBool($attribute);
    }
}
