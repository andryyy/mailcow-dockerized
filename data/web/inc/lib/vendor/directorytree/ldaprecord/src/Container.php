<?php

namespace LdapRecord;

use LdapRecord\Events\Dispatcher;
use LdapRecord\Events\DispatcherInterface;
use LdapRecord\Events\Logger;
use Psr\Log\LoggerInterface;

class Container
{
    /**
     * The current container instance.
     *
     * @var Container
     */
    protected static $instance;

    /**
     * The logger instance.
     *
     * @var LoggerInterface|null
     */
    protected $logger;

    /**
     * The event dispatcher instance.
     *
     * @var DispatcherInterface|null
     */
    protected $dispatcher;

    /**
     * The added connections in the container instance.
     *
     * @var Connection[]
     */
    protected $connections = [];

    /**
     * The name of the default connection.
     *
     * @var string
     */
    protected $default = 'default';

    /**
     * The events to register listeners for during initialization.
     *
     * @var array
     */
    protected $listen = [
        'LdapRecord\Auth\Events\*',
        'LdapRecord\Query\Events\*',
        'LdapRecord\Models\Events\*',
    ];

    /**
     * Get or set the current instance of the container.
     *
     * @return Container
     */
    public static function getInstance()
    {
        return static::$instance ?? static::getNewInstance();
    }

    /**
     * Set the container instance.
     *
     * @param Container|null $container
     *
     * @return Container|null
     */
    public static function setInstance(self $container = null)
    {
        return static::$instance = $container;
    }

    /**
     * Set and get a new instance of the container.
     *
     * @return Container
     */
    public static function getNewInstance()
    {
        return static::setInstance(new static);
    }

    /**
     * Add a connection to the container.
     *
     * @param Connection  $connection
     * @param string|null $name
     *
     * @return static
     */
    public static function addConnection(Connection $connection, $name = null)
    {
        return static::getInstance()->add($connection, $name);
    }

    /**
     * Remove a connection from the container.
     *
     * @param string $name
     *
     * @return void
     */
    public static function removeConnection($name)
    {
        static::getInstance()->remove($name);
    }

    /**
     * Get a connection by name or return the default.
     *
     * @param string|null $name
     *
     * @return Connection
     *
     * @throws ContainerException If the given connection does not exist.
     */
    public static function getConnection($name = null)
    {
        return static::getInstance()->get($name);
    }

    /**
     * Set the default connection name.
     *
     * @param string|null $name
     *
     * @return static
     */
    public static function setDefaultConnection($name = null)
    {
        return static::getInstance()->setDefault($name);
    }

    /**
     * Get the default connection.
     *
     * @return Connection
     */
    public static function getDefaultConnection()
    {
        return static::getInstance()->getDefault();
    }

    /**
     * Flush all of the added connections and reset the container.
     *
     * @return $this
     */
    public static function reset()
    {
        return static::getInstance()->flush();
    }

    /**
     * Get the container dispatcher instance.
     *
     * @return DispatcherInterface
     */
    public static function getEventDispatcher()
    {
        $instance = static::getInstance();

        if (! ($dispatcher = $instance->dispatcher())) {
            $instance->setDispatcher($dispatcher = new Dispatcher());
        }

        return $dispatcher;
    }

    /**
     * Set the container dispatcher instance.
     *
     * @param DispatcherInterface $dispatcher
     *
     * @return void
     */
    public static function setEventDispatcher(DispatcherInterface $dispatcher)
    {
        static::getInstance()->setDispatcher($dispatcher);
    }

    /**
     * Get the container dispatcher instance.
     *
     * @return DispatcherInterface|null
     */
    public function dispatcher()
    {
        return $this->dispatcher;
    }

    /**
     * Set the container dispatcher instance.
     *
     * @param DispatcherInterface $dispatcher
     *
     * @return void
     */
    public function setDispatcher(DispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Unset the event dispatcher instance.
     *
     * @return void
     */
    public function unsetEventDispatcher()
    {
        $this->dispatcher = null;
    }

    /**
     * Get the logger instance.
     *
     * @return LoggerInterface|null
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * Set the event logger to use.
     *
     * @param LoggerInterface $logger
     *
     * @return void
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        $this->initEventLogger();
    }

    /**
     * Initializes the event logger.
     *
     * @return void
     */
    public function initEventLogger()
    {
        $dispatcher = $this->getEventDispatcher();

        $logger = $this->newEventLogger();

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
     * @return Logger
     */
    protected function newEventLogger()
    {
        return new Logger($this->logger);
    }

    /**
     * Unset the logger instance.
     *
     * @return void
     */
    public function unsetLogger()
    {
        $this->logger = null;
    }

    /**
     * Add a new connection into the container.
     *
     * @param Connection  $connection
     * @param string|null $name
     *
     * @return $this
     */
    public function add(Connection $connection, $name = null)
    {
        $this->connections[$name ?? $this->default] = $connection;

        if ($this->dispatcher) {
            $connection->setDispatcher($this->dispatcher);
        }

        return $this;
    }

    /**
     * Remove a connection from the container.
     *
     * @param $name
     *
     * @return $this
     */
    public function remove($name)
    {
        if ($this->exists($name)) {
            unset($this->connections[$name]);
        }

        return $this;
    }

    /**
     * Return all of the connections from the container.
     *
     * @return Connection[]
     */
    public function all()
    {
        return $this->connections;
    }

    /**
     * Get a connection by name or return the default.
     *
     * @param string|null $name
     *
     * @return Connection
     *
     * @throws ContainerException If the given connection does not exist.
     */
    public function get($name = null)
    {
        if ($this->exists($name = $name ?? $this->default)) {
            return $this->connections[$name];
        }

        throw new ContainerException("The LDAP connection [$name] does not exist.");
    }

    /**
     * Return the default connection.
     *
     * @return Connection
     */
    public function getDefault()
    {
        return $this->get($this->default);
    }

    /**
     * Get the default connection name.
     *
     * @return string
     */
    public function getDefaultConnectionName()
    {
        return $this->default;
    }

    /**
     * Checks if the connection exists.
     *
     * @param $name
     *
     * @return bool
     */
    public function exists($name)
    {
        return array_key_exists($name, $this->connections);
    }

    /**
     * Set the default connection name.
     *
     * @param string $name
     *
     * @return $this
     */
    public function setDefault($name = null)
    {
        $this->default = $name;

        return $this;
    }

    /**
     * Flush the container of all instances and connections.
     *
     * @return $this
     */
    public function flush()
    {
        $this->connections = [];
        $this->dispatcher = null;
        $this->logger = null;

        return $this;
    }
}
