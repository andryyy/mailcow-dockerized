<?php

namespace LdapRecord\Models\DirectoryServer;

class User extends Entry
{
    /**
     * The object classes of the LDAP model.
     *
     * @var array
     */
    public static $objectClasses = [
        'top',
        'nsPerson',
        'nsAccount',
        'nsOrgPerson',
        'posixAccount',
    ];
}
