<?php

namespace LdapRecord\Models\FreeIPA;

class User extends Entry
{
    /**
     * The object classes of the LDAP model.
     *
     * @var array
     */
    public static $objectClasses = [
        'top',
        'person',
        'inetorgperson',
        'organizationalperson',
    ];

    /**
     * Retrieve groups that the current user is apart of.
     *
     * @return \LdapRecord\Models\Relations\HasMany
     */
    public function groups()
    {
        return $this->hasMany(Group::class, 'member');
    }
}
