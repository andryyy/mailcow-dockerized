<?php

namespace Adldap\Models\Concerns;

trait HasDescription
{
    /**
     * Returns the models's description.
     *
     * @link https://msdn.microsoft.com/en-us/library/ms675492(v=vs.85).aspx
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->getFirstAttribute($this->schema->description());
    }

    /**
     * Sets the models's description.
     *
     * @param string $description
     *
     * @return $this
     */
    public function setDescription($description)
    {
        return $this->setFirstAttribute($this->schema->description(), $description);
    }
}
