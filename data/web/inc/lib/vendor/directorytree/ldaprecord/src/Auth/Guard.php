<?php

namespace LdapRecord\Auth;

use Exception;
use LdapRecord\Auth\Events\Attempting;
use LdapRecord\Auth\Events\Binding;
use LdapRecord\Auth\Events\Bound;
use LdapRecord\Auth\Events\Failed;
use LdapRecord\Auth\Events\Passed;
use LdapRecord\Configuration\DomainConfiguration;
use LdapRecord\Events\DispatcherInterface;
use LdapRecord\LdapInterface;

class Guard
{
    /**
     * The connection to bind to.
     *
     * @var LdapInterface
     */
    protected $connection;

    /**
     * The domain configuration to utilize.
     *
     * @var DomainConfiguration
     */
    protected $configuration;

    /**
     * The event dispatcher.
     *
     * @var DispatcherInterface
     */
    protected $events;

    /**
     * Constructor.
     *
     * @param LdapInterface       $connection
     * @param DomainConfiguration $configuration
     */
    public function __construct(LdapInterface $connection, DomainConfiguration $configuration)
    {
        $this->connection = $connection;
        $this->configuration = $configuration;
    }

    /**
     * Attempt binding a user to the LDAP server.
     *
     * @param string $username
     * @param string $password
     * @param bool   $stayBound
     *
     * @return bool
     *
     * @throws UsernameRequiredException
     * @throws PasswordRequiredException
     */
    public function attempt($username, $password, $stayBound = false)
    {
        switch (true) {
            case empty($username):
                throw new UsernameRequiredException('A username must be specified.');
            case empty($password):
                throw new PasswordRequiredException('A password must be specified.');
        }

        $this->fireAttemptingEvent($username, $password);

        try {
            $this->bind($username, $password);

            $authenticated = true;

            $this->firePassedEvent($username, $password);
        } catch (BindException $e) {
            $authenticated = false;
        }

        if (! $stayBound) {
            $this->bindAsConfiguredUser();
        }

        return $authenticated;
    }

    /**
     * Attempt binding a user to the LDAP server. Supports anonymous binding.
     *
     * @param string|null $username
     * @param string|null $password
     *
     * @throws BindException
     * @throws \LdapRecord\ConnectionException
     */
    public function bind($username = null, $password = null)
    {
        $this->fireBindingEvent($username, $password);

        // Prior to binding, we will upgrade our connectivity to TLS on our current
        // connection and ensure we are not already bound before upgrading.
        // This is to prevent subsequent upgrading on several binds.
        if ($this->connection->isUsingTLS() && ! $this->connection->isBound()) {
            $this->connection->startTLS();
        }

        try {
            if (! $this->connection->bind($username, $password)) {
                throw new Exception($this->connection->getLastError(), $this->connection->errNo());
            }

            $this->fireBoundEvent($username, $password);
        } catch (Exception $e) {
            $this->fireFailedEvent($username, $password);

            throw BindException::withDetailedError($e, $this->connection->getDetailedError());
        }
    }

    /**
     * Bind to the LDAP server using the configured username and password.
     *
     * @throws BindException
     * @throws \LdapRecord\ConnectionException
     * @throws \LdapRecord\Configuration\ConfigurationException
     */
    public function bindAsConfiguredUser()
    {
        $this->bind(
            $this->configuration->get('username'),
            $this->configuration->get('password')
        );
    }

    /**
     * Get the event dispatcher instance.
     *
     * @return DispatcherInterface
     */
    public function getDispatcher()
    {
        return $this->events;
    }

    /**
     * Set the event dispatcher instance.
     *
     * @param DispatcherInterface $dispatcher
     *
     * @return void
     */
    public function setDispatcher(DispatcherInterface $dispatcher)
    {
        $this->events = $dispatcher;
    }

    /**
     * Fire the attempting event.
     *
     * @param string $username
     * @param string $password
     *
     * @return void
     */
    protected function fireAttemptingEvent($username, $password)
    {
        if (isset($this->events)) {
            $this->events->fire(new Attempting($this->connection, $username, $password));
        }
    }

    /**
     * Fire the passed event.
     *
     * @param string $username
     * @param string $password
     *
     * @return void
     */
    protected function firePassedEvent($username, $password)
    {
        if (isset($this->events)) {
            $this->events->fire(new Passed($this->connection, $username, $password));
        }
    }

    /**
     * Fire the failed event.
     *
     * @param string $username
     * @param string $password
     *
     * @return void
     */
    protected function fireFailedEvent($username, $password)
    {
        if (isset($this->events)) {
            $this->events->fire(new Failed($this->connection, $username, $password));
        }
    }

    /**
     * Fire the binding event.
     *
     * @param string $username
     * @param string $password
     *
     * @return void
     */
    protected function fireBindingEvent($username, $password)
    {
        if (isset($this->events)) {
            $this->events->fire(new Binding($this->connection, $username, $password));
        }
    }

    /**
     * Fire the bound event.
     *
     * @param string $username
     * @param string $password
     *
     * @return void
     */
    protected function fireBoundEvent($username, $password)
    {
        if (isset($this->events)) {
            $this->events->fire(new Bound($this->connection, $username, $password));
        }
    }
}
