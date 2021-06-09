<?php

namespace Adldap\Models\Concerns;

use Adldap\Models\Attributes\AccountControl;

trait HasUserAccountControl
{
    /**
     * Returns the users user account control integer.
     *
     * @return string
     */
    public function getUserAccountControl()
    {
        return $this->getFirstAttribute($this->schema->userAccountControl());
    }

    /**
     * Returns the users user account control as an AccountControl object.
     *
     * @return AccountControl
     */
    public function getUserAccountControlObject()
    {
        return new AccountControl($this->getUserAccountControl());
    }

    /**
     * Sets the users account control property.
     *
     * @param int|string|AccountControl $accountControl
     *
     * @return $this
     */
    public function setUserAccountControl($accountControl)
    {
        return $this->setAttribute($this->schema->userAccountControl(), (string) $accountControl);
    }

    /**
     * Returns if the user is disabled.
     *
     * @return bool
     */
    public function isDisabled()
    {
        return ($this->getUserAccountControl() & AccountControl::ACCOUNTDISABLE) === AccountControl::ACCOUNTDISABLE;
    }

    /**
     * Returns if the user is enabled.
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->getUserAccountControl() === null ? false : !$this->isDisabled();
    }
}
