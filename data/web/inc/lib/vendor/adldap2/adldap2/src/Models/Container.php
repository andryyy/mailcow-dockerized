<?php

namespace Adldap\Models;

/**
 * Class Container.
 *
 * Represents an LDAP container.
 */
class Container extends Entry
{
    use Concerns\HasDescription;
    use Concerns\HasCriticalSystemObject;

    /**
     * Returns the containers system flags integer.
     *
     * An integer value that contains flags that define additional properties of the class.
     *
     * @link https://msdn.microsoft.com/en-us/library/ms680022(v=vs.85).aspx
     *
     * @return string
     */
    public function getSystemFlags()
    {
        return $this->getFirstAttribute($this->schema->systemFlags());
    }
}
