<?php

namespace LdapRecord\Models\FreeIPA;

class Group extends Entry
{
    /**
     * The object classes of the LDAP model.
     *
     * @var array
     */
    public static $objectClasses = [
        'top',
        'groupofnames',
        'nestedgroup',
        'ipausergroup',
        'posixgroup',
    ];

    /**
     * The groups relationship.
     *
     * Retrieves groups that the current group is apart of.
     *
     * @return \LdapRecord\Models\Relations\HasMany
     */
    public function groups()
    {
        return $this->hasMany(self::class, 'member');
    }

    /**
     * Retrieve the members of the group.
     *
     * @return \LdapRecord\Models\Relations\HasMany
     */
    public function members()
    {
        return $this->hasMany(User::class, 'memberof')->using($this, 'member');
    }
}
