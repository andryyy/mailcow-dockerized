<?php

namespace LdapRecord\Models\ActiveDirectory;

class ForeignSecurityPrincipal extends Entry
{
    /**
     * The object classes of the LDAP model.
     *
     * @var array
     */
    public static $objectClasses = ['foreignsecurityprincipal'];

    /**
     * The groups relationship.
     *
     * Retrieves groups that the current security principal is apart of.
     *
     * @return \LdapRecord\Models\Relations\HasMany
     */
    public function groups()
    {
        return $this->hasMany(Group::class, 'member');
    }
}
