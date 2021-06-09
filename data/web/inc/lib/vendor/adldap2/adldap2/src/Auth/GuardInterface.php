<?php

namespace Adldap\Auth;

use Adldap\Connections\ConnectionInterface;
use Adldap\Configuration\DomainConfiguration;

interface GuardInterface
{
    /**
     * Constructor.
     *
     * @param ConnectionInterface $connection
     * @param DomainConfiguration $configuration
     */
    public function __construct(ConnectionInterface $connection, DomainConfiguration $configuration);

    /**
     * Authenticates a user using the specified credentials.
     *
     * @param string $username   The users LDAP username.
     * @param string $password   The users LDAP password.
     * @param bool   $bindAsUser Whether or not to bind as the user.
     *
     * @throws \Adldap\Auth\BindException             When re-binding to your LDAP server fails.
     * @throws \Adldap\Auth\UsernameRequiredException When username is empty.
     * @throws \Adldap\Auth\PasswordRequiredException When password is empty.
     *
     * @return bool
     */
    public function attempt($username, $password, $bindAsUser = false);

    /**
     * Binds to the current connection using the inserted credentials.
     *
     * @param string|null $username
     * @param string|null $password
     *
     * @throws \Adldap\Auth\BindException              If binding to the LDAP server fails.
     * @throws \Adldap\Connections\ConnectionException If upgrading the connection to TLS fails
     *
     * @return void
     */
    public function bind($username = null, $password = null);

    /**
     * Binds to the current LDAP server using the
     * configuration administrator credentials.
     *
     * @throws \Adldap\Auth\BindException When binding as your administrator account fails.
     *
     * @return void
     */
    public function bindAsAdministrator();
}
