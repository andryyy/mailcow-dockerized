<?php

namespace LdapRecord\Models\DirectoryServer;

use LdapRecord\Models\Model;

class Entry extends Model
{
    /**
     * The attribute key that contains the models object GUID.
     *
     * @var string
     */
    protected $guidKey = 'gidNumber';
}
