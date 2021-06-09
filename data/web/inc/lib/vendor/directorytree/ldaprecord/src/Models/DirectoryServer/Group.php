<?php

namespace LdapRecord\Models\DirectoryServer;

class Group extends Entry
{
    /**
     * The object classes of the LDAP model.
     *
     * @var array
     */
    public static $objectClasses = [
        'top',
        'groupOfUniqueNames',
        'posixGroup',
    ];
}
