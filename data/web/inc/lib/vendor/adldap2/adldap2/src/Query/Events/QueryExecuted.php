<?php

namespace Adldap\Query\Events;

use Adldap\Query\Builder;

class QueryExecuted
{
    /**
     * The LDAP filter that was used for the query.
     *
     * @var string
     */
    protected $query;

    /**
     * The number of milliseconds it took to execute the query.
     *
     * @var float
     */
    protected $time;

    /**
     * Constructor.
     *
     * @param Builder    $query
     * @param null|float $time
     */
    public function __construct(Builder $query, $time = null)
    {
        $this->query = $query;
        $this->time = $time;
    }

    /**
     * Returns the LDAP filter that was used for the query.
     *
     * @return Builder
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Returns the number of milliseconds it took to execute the query.
     *
     * @return float|null
     */
    public function getTime()
    {
        return $this->time;
    }
}
