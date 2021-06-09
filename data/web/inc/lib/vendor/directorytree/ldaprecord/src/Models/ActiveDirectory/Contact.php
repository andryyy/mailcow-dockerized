<?php

namespace LdapRecord\Models\ActiveDirectory;

class Contact extends Entry
{
    /**
     * The object classes of the LDAP model.
     *
     * @var array
     */
    public static $objectClasses = [
        'top',
        'person',
        'organizationalperson',
        'contact',
    ];

    /**
     * The groups relationship.
     *
     * Retrieves groups that the current contact is apart of.
     *
     * @return \LdapRecord\Models\Relations\HasMany
     */
    public function groups()
    {
        return $this->hasMany(Group::class, 'member');
    }
}
