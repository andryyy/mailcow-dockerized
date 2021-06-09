<?php

namespace LdapRecord\Models;

trait DetectsResetIntegers
{
    /**
     * Determine if the given value is an LDAP reset integer.
     *
     * The integer values '0' and '-1' can be used on certain
     * LDAP attributes to instruct the server to reset the
     * value to an 'unset' or 'cleared' state.
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function valueIsResetInteger($value)
    {
        return in_array($value, [0, -1], $strict = true);
    }
}
