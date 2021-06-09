<?php

namespace LdapRecord\Models\OpenLDAP;

use Illuminate\Contracts\Auth\Authenticatable;
use LdapRecord\Models\Concerns\CanAuthenticate;
use LdapRecord\Models\Concerns\HasPassword;

class User extends Entry implements Authenticatable
{
    use HasPassword;
    use CanAuthenticate;

    /**
     * The password's attribute name.
     *
     * @var string
     */
    protected $passwordAttribute = 'userpassword';

    /**
     * The password's hash method.
     *
     * @var string
     */
    protected $passwordHashMethod = 'ssha';

    /**
     * The object classes of the LDAP model.
     *
     * @var array
     */
    public static $objectClasses = [
        'top',
        'person',
        'organizationalperson',
        'inetorgperson',
    ];

    /**
     * The groups relationship.
     *
     * Retrieves groups that the user is apart of.
     *
     * @return \LdapRecord\Models\Relations\HasMany
     */
    public function groups()
    {
        return $this->hasMany(Group::class, 'memberuid', 'uid');
    }
}
