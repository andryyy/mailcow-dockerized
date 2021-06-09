<?php

namespace LdapRecord\Auth\Events;

use LdapRecord\LdapInterface;

abstract class Event
{
    /**
     * The connection that the username and password is being bound on.
     *
     * @var LdapInterface
     */
    protected $connection;

    /**
     * The username that is being used for binding.
     *
     * @var string
     */
    protected $username;

    /**
     * The password that is being used for binding.
     *
     * @var string
     */
    protected $password;

    /**
     * Constructor.
     *
     * @param LdapInterface $connection
     * @param string        $username
     * @param string        $password
     */
    public function __construct(LdapInterface $connection, $username, $password)
    {
        $this->connection = $connection;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Returns the events connection.
     *
     * @return LdapInterface
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Returns the authentication events username.
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Returns the authentication events password.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }
}
