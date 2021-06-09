<?php

namespace LdapRecord\Models\ActiveDirectory;

class Printer extends Entry
{
    /**
     * The object classes of the LDAP model.
     *
     * @var array
     */
    public static $objectClasses = ['printqueue'];
}
