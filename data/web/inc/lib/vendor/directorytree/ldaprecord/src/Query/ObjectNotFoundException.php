<?php

namespace LdapRecord\Query;

use LdapRecord\LdapRecordException;

class ObjectNotFoundException extends LdapRecordException
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
     * Create a new exception for the executed filter.
     *
     * @param string $query
     * @param null   $baseDn
     *
     * @return static
     */
    public static function forQuery($query, $baseDn = null)
    {
        return (new static())->setQuery($query, $baseDn);
    }

    /**
     * Set the query that was used.
     *
     * @param string      $query
     * @param string|null $baseDn
     *
     * @return $this
     */
    public function setQuery($query, $baseDn = null)
    {
        $this->query = $query;
        $this->baseDn = $baseDn;
        $this->message = "No LDAP query results for filter: [$query] in: [$baseDn]";

        return $this;
    }
}
