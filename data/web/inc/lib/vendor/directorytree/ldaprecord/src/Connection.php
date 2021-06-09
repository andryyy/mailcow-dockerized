<?php

namespace LdapRecord;

use Carbon\Carbon;
use Closure;
use LdapRecord\Auth\Guard;
use LdapRecord\Configuration\DomainConfiguration;
use LdapRecord\Events\DispatcherInterface;
use LdapRecord\Query\Builder;
use LdapRecord\Query\Cache;
use Psr\SimpleCache\CacheInterface;

class Connection
{
    use DetectsErrors;

    /**
     * The underlying LDAP connection.
     *
     * @var Ldap
     */
    protected $ldap;

    /**
     * The cache driver.
     *
     * @var Cache|null
     */
    protected $cache;

    /**
     * The domain configuration.
     *
     * @var DomainConfiguration
     */
    protected $configuration;

    /**
     * The event dispatcher;.
     *
     * @var DispatcherInterface|null
     */
    protected $dispatcher;

    /**
     * The current host connected to.
     *
     * @var string
     */
    protected $host;

    /**
     * The configured domain hosts.
     *
     * @var array
     */
    protected $hosts = [];

    /**
     * The attempted hosts that failed connecting to.
     *
     * @var array
     */
    protected $attempted = [];

    /**
     * The callback to execute upon total connection failure.
     *
     * @var Closure
     */
    protected $failed;

    /**
     * The authentication guard resolver.
     *
     * @var \Closure
     */
    protected $authGuardResolver;

    /**
     * Whether the connection is retrying the initial connection attempt.
     *
     * @var bool
     */
    protected $retryingInitialConnection = false;

    /**
     * Constructor.
     *
     * @param array              $config
     * @param LdapInterface|null $ldap
     */
    public function __construct($config = [], LdapInterface $ldap = null)
    {
        $this->setConfiguration($config);

        $this->setLdapConnection($ldap ?? new Ldap());

        $this->failed = function () {
            $this->dispatch(new Events\ConnectionFailed($this));
        };

        $this->authGuardResolver = function () {
            return new Guard($this->ldap, $this->configuration);
        };
    }

    /**
     * Set the connection configuration.
     *
     * @param array $config
     *
     * @return $this
     *
     * @throws Configuration\ConfigurationException
     */
    public function setConfiguration($config = [])
    {
        $this->configuration = new DomainConfiguration($config);

        $this->hosts = $this->configuration->get('hosts');

        $this->host = reset($this->hosts);

        return $this;
    }

    /**
     * Set the LDAP connection.
     *
     * @param LdapInterface $ldap
     *
     * @return $this
     */
    public function setLdapConnection(LdapInterface $ldap)
    {
        $this->ldap = $ldap;

        return $this;
    }

    /**
     * Set the event dispatcher.
     *
     * @param DispatcherInterface $dispatcher
     *
     * @return $this
     */
    public function setDispatcher(DispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;

        return $this;
    }

    /**
     * Initializes the LDAP connection.
     *
     * @return void
     */
    public function initialize()
    {
        $this->configure();

        $this->ldap->connect($this->host, $this->configuration->get('port'));
    }

    /**
     * Configure the LDAP connection.
     *
     * @return void
     */
    protected function configure()
    {
        if ($this->configuration->get('use_ssl')) {
            $this->ldap->ssl();
        } elseif ($this->configuration->get('use_tls')) {
            $this->ldap->tls();
        }

        $this->ldap->setOptions(array_replace(
            $this->configuration->get('options'),
            [
                LDAP_OPT_PROTOCOL_VERSION => $this->configuration->get('version'),
                LDAP_OPT_NETWORK_TIMEOUT  => $this->configuration->get('timeout'),
                LDAP_OPT_REFERRALS        => $this->configuration->get('follow_referrals'),
            ]
        ));
    }

    /**
     * Set the cache store.
     *
     * @param CacheInterface $store
     *
     * @return $this
     */
    public function setCache(CacheInterface $store)
    {
        $this->cache = new Cache($store);

        return $this;
    }

    /**
     * Get the cache store.
     *
     * @return Cache|null
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * Get the LDAP configuration instance.
     *
     * @return DomainConfiguration
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * Get the LDAP connection instance.
     *
     * @return Ldap
     */
    public function getLdapConnection()
    {
        return $this->ldap;
    }

    /**
     * Bind to the LDAP server.
     *
     * If no username or password is specified, then the configured credentials are used.
     *
     * @param string|null $username
     * @param string|null $password
     *
     * @return Connection
     *
     * @throws Auth\BindException
     * @throws LdapRecordException
     */
    public function connect($username = null, $password = null)
    {
        $attempt = function () use ($username, $password) {
            $this->dispatch(new Events\Connecting($this));

            is_null($username) && is_null($password)
                ? $this->auth()->bindAsConfiguredUser()
                : $this->auth()->bind($username, $password);

            $this->dispatch(new Events\Connected($this));

            $this->retryingInitialConnection = false;
        };

        try {
            $this->runOperationCallback($attempt);
        } catch (LdapRecordException $e) {
            $this->retryingInitialConnection = true;

            $this->retryOnNextHost($e, $attempt);
        }

        return $this;
    }

    /**
     * Reconnect to the LDAP server.
     *
     * @return void
     *
     * @throws Auth\BindException
     * @throws ConnectionException
     */
    public function reconnect()
    {
        $this->reinitialize();

        $this->connect();
    }

    /**
     * Reinitialize the connection.
     *
     * @return void
     */
    protected function reinitialize()
    {
        $this->disconnect();

        $this->initialize();
    }

    /**
     * Disconnect from the LDAP server.
     *
     * @return void
     */
    public function disconnect()
    {
        $this->ldap->close();
    }

    /**
     * Dispatch an event.
     *
     * @param object $event
     *
     * @return void
     */
    public function dispatch($event)
    {
        if (isset($this->dispatcher)) {
            $this->dispatcher->dispatch($event);
        }
    }

    /**
     * Get the attempted hosts that failed connecting to.
     *
     * @return array
     */
    public function attempted()
    {
        return $this->attempted;
    }

    /**
     * Perform the operation on the LDAP connection.
     *
     * @param Closure $operation
     *
     * @return mixed
     */
    public function run(Closure $operation)
    {
        try {
            // Before running the operation, we will check if the current
            // connection is bound and connect if necessary. Otherwise
            // some LDAP operations will not be executed properly.
            if (! $this->isConnected()) {
                $this->connect();
            }

            return $this->runOperationCallback($operation);
        } catch (LdapRecordException $e) {
            if ($exception = $this->getExceptionForCauseOfFailure($e)) {
                throw $exception;
            }

            return $this->tryAgainIfCausedByLostConnection($e, $operation);
        }
    }

    /**
     * Attempt to get an exception for the cause of failure.
     *
     * @param LdapRecordException $e
     *
     * @return mixed
     */
    protected function getExceptionForCauseOfFailure(LdapRecordException $e)
    {
        switch (true) {
            case $this->errorContainsMessage($e->getMessage(), 'Already exists'):
                return Exceptions\AlreadyExistsException::withDetailedError($e, $e->getDetailedError());
            case $this->errorContainsMessage($e->getMessage(), 'Insufficient access'):
                return Exceptions\InsufficientAccessException::withDetailedError($e, $e->getDetailedError());
            case $this->errorContainsMessage($e->getMessage(), 'Constraint violation'):
                return Exceptions\ConstraintViolationException::withDetailedError($e, $e->getDetailedError());
            default:
                return;
        }
    }

    /**
     * Run the operation callback on the current LDAP connection.
     *
     * @param Closure $operation
     *
     * @return mixed
     *
     * @throws LdapRecordException
     */
    protected function runOperationCallback(Closure $operation)
    {
        return $operation($this->ldap);
    }

    /**
     * Get a new auth guard instance.
     *
     * @return Auth\Guard
     */
    public function auth()
    {
        if (! $this->ldap->isConnected()) {
            $this->initialize();
        }

        $guard = call_user_func($this->authGuardResolver);

        $guard->setDispatcher(
            Container::getInstance()->getEventDispatcher()
        );

        return $guard;
    }

    /**
     * Get a new query builder for the connection.
     *
     * @return Query\Builder
     */
    public function query()
    {
        return (new Builder($this))
            ->setCache($this->cache)
            ->setBaseDn($this->configuration->get('base_dn'));
    }

    /**
     * Determine if the LDAP connection is bound.
     *
     * @return bool
     */
    public function isConnected()
    {
        return $this->ldap->isBound();
    }

    /**
     * Attempt to retry an LDAP operation if due to a lost connection.
     *
     * @param LdapRecordException $e
     * @param Closure             $operation
     *
     * @return mixed
     *
     * @throws LdapRecordException
     */
    protected function tryAgainIfCausedByLostConnection(LdapRecordException $e, Closure $operation)
    {
        // If the operation failed due to a lost or failed connection,
        // we'll attempt reconnecting and running the operation again
        // underneath the same host, and then move onto the next.
        if ($this->causedByLostConnection($e->getMessage())) {
            return $this->retry($operation);
        }

        throw $e;
    }

    /**
     * Retry the operation on the current host.
     *
     * @param Closure $operation
     *
     * @return mixed
     *
     * @throws LdapRecordException
     */
    protected function retry(Closure $operation)
    {
        try {
            $this->retryingInitialConnection
                ? $this->reinitialize()
                : $this->reconnect();

            return $this->runOperationCallback($operation);
        } catch (LdapRecordException $e) {
            return $this->retryOnNextHost($e, $operation);
        }
    }

    /**
     * Attempt the operation again on the next host.
     *
     * @param LdapRecordException $e
     * @param Closure             $operation
     *
     * @return mixed
     *
     * @throws LdapRecordException
     */
    protected function retryOnNextHost(LdapRecordException $e, Closure $operation)
    {
        $this->attempted[$this->host] = Carbon::now();

        if (($key = array_search($this->host, $this->hosts)) !== false) {
            unset($this->hosts[$key]);
        }

        if ($next = reset($this->hosts)) {
            $this->host = $next;

            return $this->tryAgainIfCausedByLostConnection($e, $operation);
        }

        call_user_func($this->failed, $this->ldap);

        throw $e;
    }
}
