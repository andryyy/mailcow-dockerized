<?php

namespace LdapRecord\Testing;

use LdapRecord\Auth\Guard;

class AuthGuardFake extends Guard
{
    /**
     * Always allow binding as configured user.
     *
     * @return bool
     */
    public function bindAsConfiguredUser()
    {
        return true;
    }
}
