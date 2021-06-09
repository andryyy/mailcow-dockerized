<?php

namespace LdapRecord\Models\ActiveDirectory;

class Group extends Entry
{
    /**
     * The object classes of the LDAP model.
     *
     * @var array
     */
    public static $objectClasses = [
        'top',
        'group',
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
        return $this->hasMany(static::class, 'member');
    }

    /**
     * The members relationship.
     *
     * Retrieves members that are apart of the group.
     *
     * @return \LdapRecord\Models\Relations\HasMany
     */
    public function members()
    {
        return $this->hasMany([
            static::class, User::class, Contact::class, Computer::class,
        ], 'memberof')
            ->using($this, 'member')
            ->with($this->primaryGroupMembers());
    }

    /**
     * The primary group members relationship.
     *
     * Retrieves members that are apart the primary group.
     *
     * @return \LdapRecord\Models\Relations\HasMany
     */
    public function primaryGroupMembers()
    {
        return $this->hasMany([
            static::class, User::class, Contact::class, Computer::class,
        ], 'primarygroupid', 'rid');
    }

    /**
     * Get the RID of the group.
     *
     * @return array
     */
    public function getRidAttribute()
    {
        $objectSidComponents = explode('-', $this->getConvertedSid());

        return [end($objectSidComponents)];
    }
}
