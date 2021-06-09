<?php

namespace LdapRecord\Events;

use LdapRecord\Connection;

abstract class ConnectionEvent
{
    /**
     * The LDAP connection.
     *
     * @var Connection
     */
    protected $connection;

    /**
     * Constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Get the connection pertaining to the event.
     *
     * @return Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }
}
