<?php

namespace Adldap\Models;

/**
 * Class Organization.
 *
 * Represents an LDAP organization.
 */
class Organization extends Entry
{
    use Concerns\HasDescription;

    /**
     * Retrieves the organization units OU attribute.
     *
     * @return string
     */
    public function getOrganization()
    {
        return $this->getFirstAttribute($this->schema->organizationName());
    }

    /**
     * {@inheritdoc}
     */
    protected function getCreatableDn()
    {
        return $this->getDnBuilder()->addO($this->getOrganization());
    }
}
