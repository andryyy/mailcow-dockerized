<?php

namespace LdapRecord\Models\ActiveDirectory;

use Illuminate\Contracts\Auth\Authenticatable;
use LdapRecord\Models\ActiveDirectory\Concerns\HasPrimaryGroup;
use LdapRecord\Models\ActiveDirectory\Scopes\RejectComputerObjectClass;
use LdapRecord\Models\Concerns\CanAuthenticate;
use LdapRecord\Models\Concerns\HasPassword;
use LdapRecord\Query\Model\Builder;

class User extends Entry implements Authenticatable
{
    use HasPassword;
    use HasPrimaryGroup;
    use CanAuthenticate;

    /**
     * The password's attribute name.
     *
     * @var string
     */
    protected $passwordAttribute = 'unicodepwd';

    /**
     * The password's hash method.
     *
     * @var string
     */
    protected $passwordHashMethod = 'encode';

    /**
     * The object classes of the LDAP model.
     *
     * @var array
     */
    public static $objectClasses = [
        'top',
        'person',
        'organizationalperson',
        'user',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'lastlogon'          => 'windows-int',
        'lastlogoff'         => 'windows-int',
        'pwdlastset'         => 'windows-int',
        'lockouttime'        => 'windows-int',
        'accountexpires'     => 'windows-int',
        'badpasswordtime'    => 'windows-int',
        'lastlogontimestamp' => 'windows-int',
    ];

    /**
     * @inheritdoc
     */
    protected static function boot()
    {
        parent::boot();

        // Here we will add a global scope to reject the 'computer' object
        // class. This is needed due to computer objects containing all
        // of the ActiveDirectory 'user' object classes. Without
        // this scope, they would be included in results.
        static::addGlobalScope(new RejectComputerObjectClass());
    }

    /**
     * The groups relationship.
     *
     * Retrieves groups that the user is apart of.
     *
     * @return \LdapRecord\Models\Relations\HasMany
     */
    public function groups()
    {
        return $this->hasMany(Group::class, 'member')->with($this->primaryGroup());
    }

    /**
     * The manager relationship.
     *
     * Retrieves the manager of the user.
     *
     * @return \LdapRecord\Models\Relations\HasOne
     */
    public function manager()
    {
        return $this->hasOne(static::class, 'manager');
    }

    /**
     * The primary group relationship of the current user.
     *
     * Retrieves the primary group the user is apart of.
     *
     * @return \LdapRecord\Models\Relations\HasOne
     */
    public function primaryGroup()
    {
        return $this->hasOnePrimaryGroup(Group::class, 'primarygroupid');
    }

    /**
     * Scopes the query to exchange mailbox users.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeWhereHasMailbox(Builder $query)
    {
        return $query->whereHas('msExchMailboxGuid');
    }
}
