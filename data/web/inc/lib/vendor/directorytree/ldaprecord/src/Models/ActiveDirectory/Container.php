<?php

namespace LdapRecord\Models\ActiveDirectory;

class Container extends Entry
{
    /**
     * The object classes of the LDAP model.
     *
     * @var array
     */
    public static $objectClasses = [
        'top',
        'container',
    ];
}
