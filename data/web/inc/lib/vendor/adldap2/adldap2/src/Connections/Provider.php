<?php

namespace Adldap\Connections;

use Adldap\Adldap;
use Adldap\Auth\Guard;
use Adldap\Query\Cache;
use InvalidArgumentException;
use Adldap\Auth\GuardInterface;
use Adldap\Schemas\ActiveDirectory;
use Adldap\Schemas\SchemaInterface;
use Psr\SimpleCache\CacheInterface;
use Adldap\Models\Factory as ModelFactory;
use Adldap\Query\Factory as SearchFactory;
use Adldap\Configuration\DomainConfiguration;

/**
 * Class Provider.
 *
 * Contains the LDAP connection and domain configuration to
 * instantiate factories for retrieving and creating
 * LDAP records as well as authentication (binding).
 */
class Provider implements ProviderInterface
{
    /**
     * The providers connection.
     *
     * @var ConnectionInterface
     */
    protected $connection;

    /**
     * The providers configuration.
     *
     * @var DomainConfiguration
     */
    protected $configuration;

    /**
     * The providers schema.
     *
     * @var SchemaInterface
     */
    protected $schema;

    /**
     * The providers auth guard instance.
     *
     * @var GuardInterface
     */
    protected $guard;

    /**
     * The providers cache instance.
     *
     * @var Cache|null
     */
    protected $cache;

    /**
     * {@inheritdoc}
     */
    public function __construct($configuration = [], ConnectionInterface $connection = null)
    {
        $this->setConfiguration($configuration)
            ->setConnection($connection);
    }

    /**
     * Does nothing. Implemented in order to remain backwards compatible.
     *
     * @deprecated since v10.3.0
     */
    public function __destruct()
    {
        //
    }

    /**
     * {@inheritdoc}
     */
    public function setConfiguration($configuration = [])
    {
        if (is_array($configuration)) {
            $configuration = new DomainConfiguration($configuration);
        }

        if ($configuration instanceof DomainConfiguration) {
            $this->configuration = $configuration;

            $schema = $configuration->get('schema');

            // We will update our schema here when our configuration is set.
            $this->setSchema(new $schema());

            return $this;
        }

        $class = DomainConfiguration::class;

        throw new InvalidArgumentException(
            "Configuration must be array or instance of $class"
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setConnection(ConnectionInterface $connection = null)
    {
        // We will create a standard connection if one isn't given.
        $this->connection = $connection ?: new Ldap();

        // Prepare the connection.
        $this->prepareConnection();

        // Instantiate the LDAP connection.
        $this->connection->connect(
            $this->configuration->get('hosts'),
            $this->configuration->get('port')
        );

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setSchema(SchemaInterface $schema = null)
    {
        $this->schema = $schema ?: new ActiveDirectory();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setGuard(GuardInterface $guard)
    {
        $this->guard = $guard;

        return $this;
    }

    /**
     * Sets the cache store.
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
     * {@inheritdoc}
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * {@inheritdoc}
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * {@inheritdoc}
     */
    public function getGuard()
    {
        if (!$this->guard instanceof GuardInterface) {
            $this->setGuard($this->getDefaultGuard($this->connection, $this->configuration));
        }

        return $this->guard;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultGuard(ConnectionInterface $connection, DomainConfiguration $configuration)
    {
        $guard = new Guard($connection, $configuration);

        $guard->setDispatcher(Adldap::getEventDispatcher());

        return $guard;
    }

    /**
     * {@inheritdoc}
     */
    public function make()
    {
        return new ModelFactory(
            $this->search()->newQuery()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function search()
    {
        $factory = new SearchFactory(
            $this->connection,
            $this->schema,
            $this->configuration->get('base_dn')
        );

        if ($this->cache) {
            $factory->setCache($this->cache);
        }

        return $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function auth()
    {
        return $this->getGuard();
    }

    /**
     * {@inheritdoc}
     */
    public function connect($username = null, $password = null)
    {
        // Get the default guard instance.
        $guard = $this->getGuard();

        if (is_null($username) && is_null($password)) {
            // If both the username and password are null, we'll connect to the server
            // using the configured administrator username and password.
            $guard->bindAsAdministrator();
        } else {
            // Bind to the server with the specified username and password otherwise.
            $guard->bind($username, $password);
        }

        return $this;
    }

    /**
     * Prepares the connection by setting configured parameters.
     *
     * @throws \Adldap\Configuration\ConfigurationException When configuration options requested do not exist
     *
     * @return void
     */
    protected function prepareConnection()
    {
        if ($this->configuration->get('use_ssl')) {
            $this->connection->ssl();
        } elseif ($this->configuration->get('use_tls')) {
            $this->connection->tls();
        }

        $options = array_replace(
            $this->configuration->get('custom_options'),
            [
                LDAP_OPT_PROTOCOL_VERSION => $this->configuration->get('version'),
                LDAP_OPT_NETWORK_TIMEOUT  => $this->configuration->get('timeout'),
                LDAP_OPT_REFERRALS        => $this->configuration->get('follow_referrals'),
            ]
        );

        $this->connection->setOptions($options);
    }
}
