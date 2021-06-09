<?php

namespace Adldap\Schemas;

class Directory389 extends Schema
{
    /**
     * {@inheritdoc}
     */
    public function accountName()
    {
        return 'uid';
    }

    /**
     * {@inheritdoc}
     */
    public function distinguishedName()
    {
        return 'dn';
    }

    /**
     * {@inheritdoc}
     */
    public function distinguishedNameSubKey()
    {
        //
    }

    /**
     * {@inheritdoc}
     */
    public function filterEnabled()
    {
        return sprintf('(!(%s=*))', $this->lockoutTime());
    }

    /**
     * {@inheritdoc}
     */
    public function filterDisabled()
    {
        return sprintf('(%s=*)', $this->lockoutTime());
    }

    /**
     * {@inheritdoc}
     */
    public function lockoutTime()
    {
        return 'pwdAccountLockedTime';
    }

    /**
     * {@inheritdoc}
     */
    public function objectCategory()
    {
        return 'objectclass';
    }

    /**
     * {@inheritdoc}
     */
    public function objectClassGroup()
    {
        return 'groupofnames';
    }

    /**
     * {@inheritdoc}
     */
    public function objectClassOu()
    {
        return 'organizationalUnit';
    }

    /**
     * {@inheritdoc}
     */
    public function objectClassPerson()
    {
        return 'inetorgperson';
    }

    /**
     * {@inheritdoc}
     */
    public function objectClassUser()
    {
        return 'inetorgperson';
    }

    /**
     * {@inheritdoc}
     */
    public function objectGuid()
    {
        return 'nsuniqueid';
    }

    /**
     * {@inheritdoc}
     */
    public function objectGuidRequiresConversion()
    {
        return false;
    }
}
