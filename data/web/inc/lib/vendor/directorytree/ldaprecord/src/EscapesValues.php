<?php

namespace LdapRecord;

use LdapRecord\Models\Attributes\EscapedValue;

trait EscapesValues
{
    /**
     * Prepare a value to be escaped.
     *
     * @param string $value
     * @param string $ignore
     * @param int    $flags
     *
     * @return EscapedValue
     */
    public function escape($value, $ignore = '', $flags = 0)
    {
        return new EscapedValue($value, $ignore, $flags);
    }
}
