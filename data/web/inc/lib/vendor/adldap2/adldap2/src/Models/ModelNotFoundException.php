<?php

namespace Adldap\Models;

use Adldap\AdldapException;

/**
 * Class ModelNotFoundException.
 *
 * Thrown when an LDAP record is not found.
 */
class ModelNotFoundException extends AdldapException
{
    /**
     * The query filter that was used.
     *
     * @var string
     */
    protected $query;

    /**
     * The base DN of the query that was used.
     *
     * @var string
     */
    protected $baseDn;

    /**
     * Sets the query that was used.
     *
     * @param string $query
     * @param string $baseDn
     *
     * @return ModelNotFoundException
     */
    public function setQuery($query, $baseDn)
    {
        $this->query = $query;
        $this->baseDn = $baseDn;

        $this->message = "No LDAP query results for filter: [{$query}] in: [{$baseDn}]";

        return $this;
    }
}
