<?php

namespace Adldap\Schemas;

class ActiveDirectory extends Schema
{
    /**
     * {@inheritdoc}
     */
    public function distinguishedName()
    {
        return 'distinguishedname';
    }

    /**
     * {@inheritdoc}
     */
    public function distinguishedNameSubKey()
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function filterEnabled()
    {
        return '(!(UserAccountControl:1.2.840.113556.1.4.803:=2))';
    }

    /**
     * {@inheritdoc}
     */
    public function filterDisabled()
    {
        return '(UserAccountControl:1.2.840.113556.1.4.803:=2)';
    }

    /**
     * {@inheritdoc}
     */
    public function lockoutTime()
    {
        return 'lockouttime';
    }

    /**
     * {@inheritdoc}
     */
    public function objectClassGroup()
    {
        return 'group';
    }

    /**
     * {@inheritdoc}
     */
    public function objectClassOu()
    {
        return 'organizationalunit';
    }

    /**
     * {@inheritdoc}
     */
    public function objectClassPerson()
    {
        return 'person';
    }

    /**
     * {@inheritdoc}
     */
    public function objectGuid()
    {
        return 'objectguid';
    }

    /**
     * {@inheritdoc}
     */
    public function objectGuidRequiresConversion()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function objectCategory()
    {
        return 'objectcategory';
    }
}
