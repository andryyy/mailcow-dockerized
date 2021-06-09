<?php

namespace Adldap\Models\Concerns;

trait HasLastLogonAndLogOff
{
    /**
     * Returns the models's last log off date.
     *
     * @link https://msdn.microsoft.com/en-us/library/ms676822(v=vs.85).aspx
     *
     * @return string
     */
    public function getLastLogOff()
    {
        return $this->getFirstAttribute($this->schema->lastLogOff());
    }

    /**
     * Returns the models's last log on date.
     *
     * @link https://msdn.microsoft.com/en-us/library/ms676823(v=vs.85).aspx
     *
     * @return string
     */
    public function getLastLogon()
    {
        return $this->getFirstAttribute($this->schema->lastLogOn());
    }

    /**
     * Returns the models's last log on timestamp.
     *
     * @link https://msdn.microsoft.com/en-us/library/ms676824(v=vs.85).aspx
     *
     * @return string
     */
    public function getLastLogonTimestamp()
    {
        return $this->getFirstAttribute($this->schema->lastLogOnTimestamp());
    }
}
