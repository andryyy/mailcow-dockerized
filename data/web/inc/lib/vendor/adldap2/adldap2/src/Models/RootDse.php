<?php

namespace Adldap\Models;

use DateTime;

/**
 * Class RootDse.
 *
 * Represents the LDAP connections Root DSE record.
 */
class RootDse extends Model
{
    /**
     * Returns the hosts current time in unix timestamp format.
     *
     * @return int
     */
    public function getCurrentTime()
    {
        $time = $this->getFirstAttribute($this->schema->currentTime());

        return DateTime::createFromFormat($this->timestampFormat, $time)->getTimestamp();
    }

    /**
     * Returns the hosts current time in the models date format.
     *
     * @return string
     */
    public function getCurrentTimeDate()
    {
        return (new DateTime())->setTimestamp($this->getCurrentTime())->format($this->dateFormat);
    }

    /**
     * Returns the hosts configuration naming context.
     *
     * @return string
     */
    public function getConfigurationNamingContext()
    {
        return $this->getFirstAttribute($this->schema->configurationNamingContext());
    }

    /**
     * Returns the hosts schema naming context.
     *
     * @return string
     */
    public function getSchemaNamingContext()
    {
        return $this->getFirstAttribute($this->schema->schemaNamingContext());
    }

    /**
     * Returns the hosts DNS name.
     *
     * @return string
     */
    public function getDnsHostName()
    {
        return $this->getFirstAttribute($this->schema->dnsHostName());
    }

    /**
     * Returns the current hosts server name.
     *
     * @return string
     */
    public function getServerName()
    {
        return $this->getFirstAttribute($this->schema->serverName());
    }

    /**
     * Returns the DN of the root domain NC for this DC's forest.
     *
     * @return mixed
     */
    public function getRootDomainNamingContext()
    {
        return $this->getFirstAttribute($this->schema->rootDomainNamingContext());
    }
}
