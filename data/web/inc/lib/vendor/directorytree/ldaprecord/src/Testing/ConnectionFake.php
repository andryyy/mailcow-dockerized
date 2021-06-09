<?php

namespace LdapRecord\Testing;

use LdapRecord\Auth\Guard;
use LdapRecord\Connection;
use LdapRecord\Models\Model;

class ConnectionFake extends Connection
{
    /**
     * The underlying fake LDAP connection.
     *
     * @var LdapFake
     */
    protected $ldap;

    /**
     * Whether the fake is connected.
     *
     * @var bool
     */
    protected $connected = false;

    /**
     * Make a new fake LDAP connection instance.
     *
     * @param array  $config
     * @param string $ldap
     *
     * @return static
     */
    public static function make(array $config = [], $ldap = LdapFake::class)
    {
        return new static($config, new $ldap);
    }

    /**
     * Set the user to authenticate as.
     *
     * @param Model|string $user
     *
     * @return $this
     */
    public function actingAs($user)
    {
        $this->ldap->shouldAuthenticateWith(
            $user instanceof Model ? $user->getDn() : $user
        );

        return $this;
    }

    /**
     * Set the connection to bypass bind attempts as the configured user.
     *
     * @return $this
     */
    public function shouldBeConnected()
    {
        $this->connected = true;

        $this->authGuardResolver = function () {
            return new AuthGuardFake($this->ldap, $this->configuration);
        };

        return $this;
    }

    /**
     * Set the connection to attempt binding as the configured user.
     *
     * @return $this
     */
    public function shouldNotBeConnected()
    {
        $this->connected = false;

        $this->authGuardResolver = function () {
            return new Guard($this->ldap, $this->configuration);
        };

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isConnected()
    {
        return $this->connected ?: parent::isConnected();
    }
}
