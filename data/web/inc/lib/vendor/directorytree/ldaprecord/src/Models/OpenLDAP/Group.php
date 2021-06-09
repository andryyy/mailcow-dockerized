<?php

namespace LdapRecord\Models\OpenLDAP;

class Group extends Entry
{
    /**
     * The object classes of the LDAP model.
     *
     * @var array
     */
    public static $objectClasses = [
        'top',
        'groupofuniquenames',
    ];
}
