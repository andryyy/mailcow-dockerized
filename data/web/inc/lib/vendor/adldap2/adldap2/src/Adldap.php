<?php

namespace Adldap;

use Adldap\Log\EventLogger;
use Adldap\Connections\Ldap;
use InvalidArgumentException;
use Adldap\Log\LogsInformation;
use Adldap\Connections\Provider;
use Adldap\Events\DispatchesEvents;
use Adldap\Connections\ProviderInterface;
use Adldap\Connections\ConnectionInterface;
use Adldap\Configuration\DomainConfiguration;

class Adldap implements AdldapInterface
{
    use DispatchesEvents;
    use LogsInformation;
    /**
     * The default provider name.
     *
     * @var string
     */
    protected $default = 'default';

    /**
     * The connection providers.
     *
     * @var array
     */
    protected $providers = [];

    /**
     * The events to register listeners for during initialization.
     *
     * @var array
     */
    protected $listen = [
        'Adldap\Auth\Events\*',
        'Adldap\Query\Events\*',
        'Adldap\Models\Events\*',
    ];

    /**
     * {@inheritdoc}
     */
    public function __construct(array $providers = [])
    {
        foreach ($providers as $name => $config) {
            $this->addProvider($config, $name);
        }

        if ($default = key($providers)) {
            $this->setDefaultProvider($default);
        }

        $this->initEventLogger();
    }

    /**
     * {@inheritdoc}
     */
    public function addProvider($config, $name = 'default', ConnectionInterface $connection = null)
    {
        if ($this->isValidConfig($config)) {
            $config = new Provider($config, $connection ?? new Ldap($name));
        }

        if ($config instanceof ProviderInterface) {
            $this->providers[$name] = $config;

            return $this;
        }

        throw new InvalidArgumentException(
            "You must provide a configuration array or an instance of Adldap\Connections\ProviderInterface."
        );
    }

    /**
     * Determines if the given config is valid.
     *
     * @param mixed $config
     *
     * @return bool
     */
    protected function isValidConfig($config)
    {
        return is_array($config) || $config instanceof DomainConfiguration;
    }

    /**
     * {@inheritdoc}
     */
    public function getProviders()
    {
        return $this->providers;
    }

    /**
     * {@inheritdoc}
     */
    public function getProvider($name)
    {
        if (array_key_exists($name, $this->providers)) {
            return $this->providers[$name];
        }

        throw new AdldapException("The connection provider '$name' does not exist.");
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultProvider($name = 'default')
    {
        if ($this->getProvider($name) instanceof ProviderInterface) {
            $this->default = $name;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultProvider()
    {
        return $this->getProvider($this->default);
    }

    /**
     * {@inheritdoc}
     */
    public function removeProvider($name)
    {
        unset($this->providers[$name]);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function connect($name = null, $username = null, $password = null)
    {
        $provider = $name ? $this->getProvider($name) : $this->getDefaultProvider();

        return $provider->connect($username, $password);
    }

    /**
     * {@inheritdoc}
     */
    public function __call($method, $parameters)
    {
        $provider = $this->getDefaultProvider();
        
        if (! $provider->getConnection()->isBound()) {
            $provider->connect();
        }

        return call_user_func_array([$provider, $method], $parameters);
    }

    /**
     * Initializes the event logger.
     *
     * @return void
     */
    public function initEventLogger()
    {
        $dispatcher = static::getEventDispatcher();

        $logger = $this->newEventLogger();

        // We will go through each of our event wildcards and register their listener.
        foreach ($this->listen as $event) {
            $dispatcher->listen($event, function ($eventName, $events) use ($logger) {
                foreach ($events as $event) {
                    $logger->log($event);
                }
            });
        }
    }

    /**
     * Returns a new event logger instance.
     *
     * @return EventLogger
     */
    protected function newEventLogger()
    {
        return new EventLogger(static::getLogger());
    }
}
