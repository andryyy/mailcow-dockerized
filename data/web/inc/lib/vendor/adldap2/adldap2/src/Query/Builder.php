<?php

namespace Adldap\Query;

use Closure;
use Adldap\Adldap;
use Adldap\Utilities;
use Adldap\Models\Model;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use Adldap\Schemas\ActiveDirectory;
use Adldap\Schemas\SchemaInterface;
use Adldap\Query\Events\QueryExecuted;
use Adldap\Models\ModelNotFoundException;
use Adldap\Connections\ConnectionInterface;

class Builder
{
    /**
     * The selected columns to retrieve on the query.
     *
     * @var array
     */
    public $columns = ['*'];

    /**
     * The query filters.
     *
     * @var array
     */
    public $filters = [
        'and' => [],
        'or'  => [],
        'raw' => [],
    ];

    /**
     * The size limit of the query.
     *
     * @var int
     */
    public $limit = 0;

    /**
     * Determines whether the current query is paginated.
     *
     * @var bool
     */
    public $paginated = false;

    /**
     * The field to sort search results by.
     *
     * @var string
     */
    protected $sortByField = '';

    /**
     * The direction to sort the results by.
     *
     * @var string
     */
    protected $sortByDirection = '';

    /**
     * The sort flags for sorting query results.
     *
     * @var int
     */
    protected $sortByFlags;

    /**
     * The distinguished name to perform searches upon.
     *
     * @var string|null
     */
    protected $dn;

    /**
     * The default query type.
     *
     * @var string
     */
    protected $type = 'search';

    /**
     * Determines whether or not to return LDAP results in their raw array format.
     *
     * @var bool
     */
    protected $raw = false;

    /**
     * Determines whether the query is nested.
     *
     * @var bool
     */
    protected $nested = false;

    /**
     * Determines whether the query should be cached.
     *
     * @var bool
     */
    protected $caching = false;

    /**
     * How long the query should be cached until.
     *
     * @var \DateTimeInterface|null
     */
    protected $cacheUntil = null;

    /**
     * Determines whether the query cache must be flushed.
     *
     * @var bool
     */
    protected $flushCache = false;

    /**
     * The current connection instance.
     *
     * @var ConnectionInterface
     */
    protected $connection;

    /**
     * The current grammar instance.
     *
     * @var Grammar
     */
    protected $grammar;

    /**
     * The current schema instance.
     *
     * @var SchemaInterface
     */
    protected $schema;

    /**
     * The current cache instance.
     *
     * @var Cache|null
     */
    protected $cache;

    /**
     * Constructor.
     *
     * @param ConnectionInterface  $connection
     * @param Grammar|null         $grammar
     * @param SchemaInterface|null $schema
     */
    public function __construct(ConnectionInterface $connection, Grammar $grammar = null, SchemaInterface $schema = null)
    {
        $this->setConnection($connection)
            ->setGrammar($grammar)
            ->setSchema($schema);
    }

    /**
     * Sets the current connection.
     *
     * @param ConnectionInterface $connection
     *
     * @return Builder
     */
    public function setConnection(ConnectionInterface $connection)
    {
        $this->connection = $connection;

        return $this;
    }

    /**
     * Sets the current filter grammar.
     *
     * @param Grammar|null $grammar
     *
     * @return Builder
     */
    public function setGrammar(Grammar $grammar = null)
    {
        $this->grammar = $grammar ?: new Grammar();

        return $this;
    }

    /**
     * Sets the current schema.
     *
     * @param SchemaInterface|null $schema
     *
     * @return Builder
     */
    public function setSchema(SchemaInterface $schema = null)
    {
        $this->schema = $schema ?: new ActiveDirectory();

        return $this;
    }

    /**
     * Returns the current schema.
     *
     * @return SchemaInterface
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * Sets the cache to store query results.
     *
     * @param Cache|null $cache
     */
    public function setCache(Cache $cache = null)
    {
        $this->cache = $cache;

        return $this;
    }

    /**
     * Returns a new Query Builder instance.
     *
     * @param string $baseDn
     *
     * @return Builder
     */
    public function newInstance($baseDn = null)
    {
        // We'll set the base DN of the new Builder so
        // developers don't need to do this manually.
        $dn = is_null($baseDn) ? $this->getDn() : $baseDn;

        return (new static($this->connection, $this->grammar, $this->schema))
            ->setDn($dn);
    }

    /**
     * Returns a new nested Query Builder instance.
     *
     * @param Closure|null $closure
     *
     * @return $this
     */
    public function newNestedInstance(Closure $closure = null)
    {
        $query = $this->newInstance()->nested();

        if ($closure) {
            call_user_func($closure, $query);
        }

        return $query;
    }

    /**
     * Returns the current query.
     *
     * @return Collection|array
     */
    public function get()
    {
        // We'll mute any warnings / errors here. We just need to
        // know if any query results were returned.
        return @$this->query($this->getQuery());
    }

    /**
     * Compiles and returns the current query string.
     *
     * @return string
     */
    public function getQuery()
    {
        // We need to ensure we have at least one filter, as
        // no query results will be returned otherwise.
        if (count(array_filter($this->filters)) === 0) {
            $this->whereHas($this->schema->objectClass());
        }

        return $this->grammar->compile($this);
    }

    /**
     * Returns the unescaped query.
     *
     * @return string
     */
    public function getUnescapedQuery()
    {
        return Utilities::unescape($this->getQuery());
    }

    /**
     * Returns the current Grammar instance.
     *
     * @return Grammar
     */
    public function getGrammar()
    {
        return $this->grammar;
    }

    /**
     * Returns the current Connection instance.
     *
     * @return ConnectionInterface
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Returns the builders DN to perform searches upon.
     *
     * @return string
     */
    public function getDn()
    {
        return $this->dn;
    }

    /**
     * Sets the DN to perform searches upon.
     *
     * @param string|Model|null $dn
     *
     * @return Builder
     */
    public function setDn($dn = null)
    {
        $this->dn = $dn instanceof Model ? $dn->getDn() : $dn;

        return $this;
    }

    /**
     * Alias for setting the base DN of the query.
     *
     * @param string|Model|null $dn
     *
     * @return Builder
     */
    public function in($dn = null)
    {
        return $this->setDn($dn);
    }

    /**
     * Sets the size limit of the current query.
     *
     * @param int $limit
     *
     * @return Builder
     */
    public function limit($limit = 0)
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * Performs the specified query on the current LDAP connection.
     *
     * @param string $query
     *
     * @return \Adldap\Query\Collection|array
     */
    public function query($query)
    {
        $start = microtime(true);

        // Here we will create the execution callback. This allows us
        // to only execute an LDAP request if caching is disabled
        // or if no cache of the given query exists yet.
        $callback = function () use ($query) {
            return $this->parse($this->run($query));
        };

        // If caching is enabled and we have a cache instance available,
        // we will try to retrieve the cached results instead.
        // Otherwise, we will simply execute the callback.
        if ($this->caching && $this->cache) {
            $results = $this->getCachedResponse($this->getCacheKey($query), $callback);
        } else {
            $results = $callback();
        }

        // Log the query.
        $this->logQuery($this, $this->type, $this->getElapsedTime($start));

        // Process & return the results.
        return $this->newProcessor()->process($results);
    }

    /**
     * Paginates the current LDAP query.
     *
     * @param int  $perPage
     * @param int  $currentPage
     * @param bool $isCritical
     *
     * @return Paginator
     */
    public function paginate($perPage = 1000, $currentPage = 0, $isCritical = true)
    {
        $this->paginated = true;

        $start = microtime(true);

        $query = $this->getQuery();

        // Here we will create the pagination callback. This allows us
        // to only execute an LDAP request if caching is disabled
        // or if no cache of the given query exists yet.
        $callback = function () use ($query, $perPage, $isCritical) {
            return $this->runPaginate($query, $perPage, $isCritical);
        };

        // If caching is enabled and we have a cache instance available,
        // we will try to retrieve the cached results instead.
        if ($this->caching && $this->cache) {
            $pages = $this->getCachedResponse($this->getCacheKey($query), $callback);
        } else {
            $pages = $callback();
        }

        // Log the query.
        $this->logQuery($this, 'paginate', $this->getElapsedTime($start));

        // Process & return the results.
        return $this->newProcessor()->processPaginated($pages, $perPage, $currentPage);
    }

    /**
     * Get the cached response or execute and cache the callback value.
     *
     * @param string  $key
     * @param Closure $callback
     *
     * @return mixed
     */
    protected function getCachedResponse($key, Closure $callback)
    {
        if ($this->flushCache) {
            $this->cache->delete($key);
        }

        return $this->cache->remember($key, $this->cacheUntil, $callback);
    }

    /**
     * Runs the query operation with the given filter.
     *
     * @param string $filter
     *
     * @return resource
     */
    protected function run($filter)
    {
        return $this->connection->{$this->type}(
            $this->getDn(),
            $filter,
            $this->getSelects(),
            $onlyAttributes = false,
            $this->limit
        );
    }

    /**
     * Runs the paginate operation with the given filter.
     *
     * @param string $filter
     * @param int    $perPage
     * @param bool   $isCritical
     *
     * @return array
     */
    protected function runPaginate($filter, $perPage, $isCritical)
    {
        return $this->connection->supportsServerControlsInMethods() ?
            $this->compatiblePaginationCallback($filter, $perPage, $isCritical) :
            $this->deprecatedPaginationCallback($filter, $perPage, $isCritical);
    }

    /**
     * Create a deprecated pagination callback compatible with PHP 7.2.
     *
     * @param string $filter
     * @param int    $perPage
     * @param bool   $isCritical
     *
     * @return array
     */
    protected function deprecatedPaginationCallback($filter, $perPage, $isCritical)
    {
        $pages = [];

        $cookie = '';

        do {
            $this->connection->controlPagedResult($perPage, $isCritical, $cookie);

            if (! $resource = $this->run($filter)) {
                break;
            }

            // If we have been given a valid resource, we will retrieve the next
            // pagination cookie to send for our next pagination request.
            $this->connection->controlPagedResultResponse($resource, $cookie);

            $pages[] = $this->parse($resource);
        } while (!empty($cookie));

        // Reset paged result on the current connection. We won't pass in the current $perPage
        // parameter since we want to reset the page size to the default '1000'. Sending '0'
        // eliminates any further opportunity for running queries in the same request,
        // even though that is supposed to be the correct usage.
        $this->connection->controlPagedResult();

        return $pages;
    }

    /**
     * Create a compatible pagination callback compatible with PHP 7.3 and greater.
     *
     * @param string $filter
     * @param int    $perPage
     * @param bool   $isCritical
     *
     * @return array
     */
    protected function compatiblePaginationCallback($filter, $perPage, $isCritical)
    {
        $pages = [];

        // Setup our paged results control.
        $controls = [
            LDAP_CONTROL_PAGEDRESULTS => [
                'oid'        => LDAP_CONTROL_PAGEDRESULTS,
                'isCritical' => $isCritical,
                'value'      => [
                    'size'   => $perPage,
                    'cookie' => '',
                ],
            ],
        ];

        do {
            // Update the server controls.
            $this->connection->setOption(LDAP_OPT_SERVER_CONTROLS, $controls);

            if (! $resource = $this->run($filter)) {
                break;
            }

            $errorCode = $dn = $errorMessage = $refs = null;

            // Update the server controls with the servers response.
            $this->connection->parseResult($resource, $errorCode, $dn, $errorMessage, $refs, $controls);

            $pages[] = $this->parse($resource);

            // Reset paged result on the current connection. We won't pass in the current $perPage
            // parameter since we want to reset the page size to the default '1000'. Sending '0'
            // eliminates any further opportunity for running queries in the same request,
            // even though that is supposed to be the correct usage.
            $controls[LDAP_CONTROL_PAGEDRESULTS]['value']['size'] = $perPage;
        } while (!empty($controls[LDAP_CONTROL_PAGEDRESULTS]['value']['cookie']));

        // After running the query, we will clear the LDAP server controls. This
        // allows the controls to be automatically reset before each new query
        // that is conducted on the same connection during each request.
        $this->connection->setOption(LDAP_OPT_SERVER_CONTROLS, []);

        return $pages;
    }

    /**
     * Parses the given LDAP resource by retrieving its entries.
     *
     * @param resource $resource
     *
     * @return array
     */
    protected function parse($resource)
    {
        // Normalize entries. Get entries returns false on failure.
        // We'll always want an array in this situation.
        $entries = $this->connection->getEntries($resource) ?: [];

        // Free up memory.
        if (is_resource($resource)) {
            $this->connection->freeResult($resource);
        }

        return $entries;
    }

    /**
     * Returns the cache key.
     *
     * @param string $query
     *
     * @return string
     */
    protected function getCacheKey($query)
    {
        $key = $this->connection->getHost()
            .$this->type
            .$this->getDn()
            .$query
            .implode('', $this->getSelects())
            .$this->limit
            .$this->paginated;

        return md5($key);
    }

    /**
     * Returns the first entry in a search result.
     *
     * @param array|string $columns
     *
     * @return Model|array|null
     */
    public function first($columns = [])
    {
        $results = $this->select($columns)->limit(1)->get();

        // Since results may be returned inside an array if `raw()`
        // is specified, then we'll use our array helper
        // to retrieve the first result.
        return Arr::get($results, 0);
    }

    /**
     * Returns the first entry in a search result.
     *
     * If no entry is found, an exception is thrown.
     *
     * @param array|string $columns
     *
     * @throws ModelNotFoundException
     *
     * @return Model|array
     */
    public function firstOrFail($columns = [])
    {
        $record = $this->first($columns);

        if (!$record) {
            throw (new ModelNotFoundException())
                ->setQuery($this->getUnescapedQuery(), $this->getDn());
        }

        return $record;
    }

    /**
     * Finds a record by the specified attribute and value.
     *
     * @param string       $attribute
     * @param string       $value
     * @param array|string $columns
     *
     * @return Model|array|false
     */
    public function findBy($attribute, $value, $columns = [])
    {
        try {
            return $this->findByOrFail($attribute, $value, $columns);
        } catch (ModelNotFoundException $e) {
            return;
        }
    }

    /**
     * Finds a record by the specified attribute and value.
     *
     * If no record is found an exception is thrown.
     *
     * @param string       $attribute
     * @param string       $value
     * @param array|string $columns
     *
     * @throws ModelNotFoundException
     *
     * @return Model|array
     */
    public function findByOrFail($attribute, $value, $columns = [])
    {
        return $this->whereEquals($attribute, $value)->firstOrFail($columns);
    }

    /**
     * Finds a record using ambiguous name resolution.
     *
     * @param string|array $value
     * @param array|string $columns
     *
     * @return Model|array|null
     */
    public function find($value, $columns = [])
    {
        if (is_array($value)) {
            return $this->findMany($value, $columns);
        }

        // If we're not using ActiveDirectory, we can't use ANR. We'll make our own query.
        if (!is_a($this->schema, ActiveDirectory::class)) {
            return $this->prepareAnrEquivalentQuery($value)->first($columns);
        }

        return $this->findBy($this->schema->anr(), $value, $columns);
    }

    /**
     * Finds multiple records using ambiguous name resolution.
     *
     * @param array $values
     * @param array $columns
     *
     * @return \Adldap\Query\Collection|array
     */
    public function findMany(array $values = [], $columns = [])
    {
        $this->select($columns);

        if (!is_a($this->schema, ActiveDirectory::class)) {
            $query = $this;

            foreach ($values as $value) {
                $query->prepareAnrEquivalentQuery($value);
            }

            return $query->get();
        }

        return $this->findManyBy($this->schema->anr(), $values);
    }

    /**
     * Creates an ANR equivalent query for LDAP distributions that do not support ANR.
     *
     * @param string $value
     *
     * @return Builder
     */
    protected function prepareAnrEquivalentQuery($value)
    {
        return $this->orFilter(function (self $query) use ($value) {
            $locateBy = [
                $this->schema->name(),
                $this->schema->email(),
                $this->schema->userId(),
                $this->schema->lastName(),
                $this->schema->firstName(),
                $this->schema->commonName(),
                $this->schema->displayName(),
            ];

            foreach ($locateBy as $attribute) {
                $query->whereEquals($attribute, $value);
            }
        });
    }

    /**
     * Finds many records by the specified attribute.
     *
     * @param string $attribute
     * @param array  $values
     * @param array  $columns
     *
     * @return \Adldap\Query\Collection|array
     */
    public function findManyBy($attribute, array $values = [], $columns = [])
    {
        $query = $this->select($columns);

        foreach ($values as $value) {
            $query->orWhere([$attribute => $value]);
        }

        return $query->get();
    }

    /**
     * Finds a record using ambiguous name resolution.
     *
     * If a record is not found, an exception is thrown.
     *
     * @param string       $value
     * @param array|string $columns
     *
     * @throws ModelNotFoundException
     *
     * @return Model|array
     */
    public function findOrFail($value, $columns = [])
    {
        $entry = $this->find($value, $columns);

        // Make sure we check if the result is an entry or an array before
        // we throw an exception in case the user wants raw results.
        if (!$entry instanceof Model && !is_array($entry)) {
            throw (new ModelNotFoundException())
                ->setQuery($this->getUnescapedQuery(), $this->getDn());
        }

        return $entry;
    }

    /**
     * Finds a record by its distinguished name.
     *
     * @param string       $dn
     * @param array|string $columns
     *
     * @return bool|Model
     */
    public function findByDn($dn, $columns = [])
    {
        try {
            return $this->findByDnOrFail($dn, $columns);
        } catch (ModelNotFoundException $e) {
            return;
        }
    }

    /**
     * Finds a record by its distinguished name.
     *
     * Fails upon no records returned.
     *
     * @param string       $dn
     * @param array|string $columns
     *
     * @throws ModelNotFoundException
     *
     * @return Model|array
     */
    public function findByDnOrFail($dn, $columns = [])
    {
        // Since we're setting our base DN to be able to retrieve a model
        // by its distinguished name, we need to set it back to
        // our configured base so it is not overwritten.
        $base = $this->getDn();

        $model = $this->setDn($dn)
            ->read()
            ->whereHas($this->schema->objectClass())
            ->firstOrFail($columns);

        // Reset the models query builder (in case a model is returned).
        // Otherwise, we must be requesting a raw result.
        if ($model instanceof Model) {
            $model->setQuery($this->in($base));
        }

        return $model;
    }

    /**
     * Finds a record by its string GUID.
     *
     * @param string       $guid
     * @param array|string $columns
     *
     * @return Model|array|false
     */
    public function findByGuid($guid, $columns = [])
    {
        try {
            return $this->findByGuidOrFail($guid, $columns);
        } catch (ModelNotFoundException $e) {
            return;
        }
    }

    /**
     * Finds a record by its string GUID.
     *
     * Fails upon no records returned.
     *
     * @param string       $guid
     * @param array|string $columns
     *
     * @throws ModelNotFoundException
     *
     * @return Model|array
     */
    public function findByGuidOrFail($guid, $columns = [])
    {
        if ($this->schema->objectGuidRequiresConversion()) {
            $guid = Utilities::stringGuidToHex($guid);
        }

        return $this->select($columns)->whereRaw([
            $this->schema->objectGuid() => $guid,
        ])->firstOrFail();
    }

    /**
     * Finds a record by its Object SID.
     *
     * @param string       $sid
     * @param array|string $columns
     *
     * @return Model|array|false
     */
    public function findBySid($sid, $columns = [])
    {
        try {
            return $this->findBySidOrFail($sid, $columns);
        } catch (ModelNotFoundException $e) {
            return;
        }
    }

    /**
     * Finds a record by its Object SID.
     *
     * Fails upon no records returned.
     *
     * @param string       $sid
     * @param array|string $columns
     *
     * @throws ModelNotFoundException
     *
     * @return Model|array
     */
    public function findBySidOrFail($sid, $columns = [])
    {
        return $this->findByOrFail($this->schema->objectSid(), $sid, $columns);
    }

    /**
     * Finds the Base DN of your domain controller.
     *
     * @return string|bool
     */
    public function findBaseDn()
    {
        $result = $this->setDn(null)
            ->read()
            ->raw()
            ->whereHas($this->schema->objectClass())
            ->first();

        $key = $this->schema->defaultNamingContext();

        if (is_array($result) && array_key_exists($key, $result)) {
            if (array_key_exists(0, $result[$key])) {
                return $result[$key][0];
            }
        }

        return false;
    }

    /**
     * Adds the inserted fields to query on the current LDAP connection.
     *
     * @param array|string $columns
     *
     * @return Builder
     */
    public function select($columns = [])
    {
        $columns = is_array($columns) ? $columns : func_get_args();

        if (!empty($columns)) {
            $this->columns = $columns;
        }

        return $this;
    }

    /**
     * Adds a raw filter to the current query.
     *
     * @param array|string $filters
     *
     * @return Builder
     */
    public function rawFilter($filters = [])
    {
        $filters = is_array($filters) ? $filters : func_get_args();

        foreach ($filters as $filter) {
            $this->filters['raw'][] = $filter;
        }

        return $this;
    }

    /**
     * Adds a nested 'and' filter to the current query.
     *
     * @param Closure $closure
     *
     * @return Builder
     */
    public function andFilter(Closure $closure)
    {
        $query = $this->newNestedInstance($closure);

        $filter = $this->grammar->compileAnd($query->getQuery());

        return $this->rawFilter($filter);
    }

    /**
     * Adds a nested 'or' filter to the current query.
     *
     * @param Closure $closure
     *
     * @return Builder
     */
    public function orFilter(Closure $closure)
    {
        $query = $this->newNestedInstance($closure);

        $filter = $this->grammar->compileOr($query->getQuery());

        return $this->rawFilter($filter);
    }

    /**
     * Adds a nested 'not' filter to the current query.
     *
     * @param Closure $closure
     *
     * @return Builder
     */
    public function notFilter(Closure $closure)
    {
        $query = $this->newNestedInstance($closure);

        $filter = $this->grammar->compileNot($query->getQuery());

        return $this->rawFilter($filter);
    }

    /**
     * Adds a where clause to the current query.
     *
     * @param string|array $field
     * @param string       $operator
     * @param string       $value
     * @param string       $boolean
     * @param bool         $raw
     *
     * @throws InvalidArgumentException
     *
     * @return Builder
     */
    public function where($field, $operator = null, $value = null, $boolean = 'and', $raw = false)
    {
        if (is_array($field)) {
            // If the column is an array, we will assume it is an array of
            // key-value pairs and can add them each as a where clause.
            return $this->addArrayOfWheres($field, $boolean, $raw);
        }

        // We'll bypass the 'has' and 'notHas' operator since they
        // only require two arguments inside the where method.
        $bypass = [Operator::$has, Operator::$notHas];

        // Here we will make some assumptions about the operator. If only
        // 2 values are passed to the method, we will assume that
        // the operator is 'equals' and keep going.
        if (func_num_args() === 2 && in_array($operator, $bypass) === false) {
            list($value, $operator) = [$operator, '='];
        }

        if (!in_array($operator, Operator::all())) {
            throw new InvalidArgumentException("Invalid where operator: {$operator}");
        }

        // We'll escape the value if raw isn't requested.
        $value = $raw ? $value : $this->escape($value);

        $field = $this->escape($field, $ignore = null, 3);

        $this->addFilter($boolean, compact('field', 'operator', 'value'));

        return $this;
    }

    /**
     * Adds a raw where clause to the current query.
     *
     * Values given to this method are not escaped.
     *
     * @param string|array $field
     * @param string       $operator
     * @param string       $value
     *
     * @return Builder
     */
    public function whereRaw($field, $operator = null, $value = null)
    {
        return $this->where($field, $operator, $value, 'and', true);
    }

    /**
     * Adds a 'where equals' clause to the current query.
     *
     * @param string $field
     * @param string $value
     *
     * @return Builder
     */
    public function whereEquals($field, $value)
    {
        return $this->where($field, Operator::$equals, $value);
    }

    /**
     * Adds a 'where not equals' clause to the current query.
     *
     * @param string $field
     * @param string $value
     *
     * @return Builder
     */
    public function whereNotEquals($field, $value)
    {
        return $this->where($field, Operator::$doesNotEqual, $value);
    }

    /**
     * Adds a 'where approximately equals' clause to the current query.
     *
     * @param string $field
     * @param string $value
     *
     * @return Builder
     */
    public function whereApproximatelyEquals($field, $value)
    {
        return $this->where($field, Operator::$approximatelyEquals, $value);
    }

    /**
     * Adds a 'where has' clause to the current query.
     *
     * @param string $field
     *
     * @return Builder
     */
    public function whereHas($field)
    {
        return $this->where($field, Operator::$has);
    }

    /**
     * Adds a 'where not has' clause to the current query.
     *
     * @param string $field
     *
     * @return Builder
     */
    public function whereNotHas($field)
    {
        return $this->where($field, Operator::$notHas);
    }

    /**
     * Adds a 'where contains' clause to the current query.
     *
     * @param string $field
     * @param string $value
     *
     * @return Builder
     */
    public function whereContains($field, $value)
    {
        return $this->where($field, Operator::$contains, $value);
    }

    /**
     * Adds a 'where contains' clause to the current query.
     *
     * @param string $field
     * @param string $value
     *
     * @return Builder
     */
    public function whereNotContains($field, $value)
    {
        return $this->where($field, Operator::$notContains, $value);
    }

    /**
     * Query for entries that match any of the values provided for the given field.
     *
     * @param string $field
     * @param array  $values
     *
     * @return Builder
     */
    public function whereIn($field, array $values)
    {
        return $this->orFilter(function (self $query) use ($field, $values) {
            foreach ($values as $value) {
                $query->whereEquals($field, $value);
            }
        });
    }

    /**
     * Adds a 'between' clause to the current query.
     *
     * @param string $field
     * @param array  $values
     *
     * @return Builder
     */
    public function whereBetween($field, array $values)
    {
        return $this->where([
            [$field, '>=', $values[0]],
            [$field, '<=', $values[1]],
        ]);
    }

    /**
     * Adds a 'where starts with' clause to the current query.
     *
     * @param string $field
     * @param string $value
     *
     * @return Builder
     */
    public function whereStartsWith($field, $value)
    {
        return $this->where($field, Operator::$startsWith, $value);
    }

    /**
     * Adds a 'where *not* starts with' clause to the current query.
     *
     * @param string $field
     * @param string $value
     *
     * @return Builder
     */
    public function whereNotStartsWith($field, $value)
    {
        return $this->where($field, Operator::$notStartsWith, $value);
    }

    /**
     * Adds a 'where ends with' clause to the current query.
     *
     * @param string $field
     * @param string $value
     *
     * @return Builder
     */
    public function whereEndsWith($field, $value)
    {
        return $this->where($field, Operator::$endsWith, $value);
    }

    /**
     * Adds a 'where *not* ends with' clause to the current query.
     *
     * @param string $field
     * @param string $value
     *
     * @return Builder
     */
    public function whereNotEndsWith($field, $value)
    {
        return $this->where($field, Operator::$notEndsWith, $value);
    }

    /**
     * Adds a enabled filter to the current query.
     *
     * @return Builder
     */
    public function whereEnabled()
    {
        return $this->rawFilter($this->schema->filterEnabled());
    }

    /**
     * Adds a disabled filter to the current query.
     *
     * @return Builder
     */
    public function whereDisabled()
    {
        return $this->rawFilter($this->schema->filterDisabled());
    }

    /**
     * Adds a 'member of' filter to the current query.
     *
     * @param string $dn
     *
     * @return Builder
     */
    public function whereMemberOf($dn)
    {
        return $this->whereEquals($this->schema->memberOfRecursive(), $dn);
    }

    /**
     * Adds an 'or where' clause to the current query.
     *
     * @param array|string $field
     * @param string|null  $operator
     * @param string|null  $value
     *
     * @return Builder
     */
    public function orWhere($field, $operator = null, $value = null)
    {
        return $this->where($field, $operator, $value, 'or');
    }

    /**
     * Adds a raw or where clause to the current query.
     *
     * Values given to this method are not escaped.
     *
     * @param string $field
     * @param string $operator
     * @param string $value
     *
     * @return Builder
     */
    public function orWhereRaw($field, $operator = null, $value = null)
    {
        return $this->where($field, $operator, $value, 'or', true);
    }

    /**
     * Adds an 'or where has' clause to the current query.
     *
     * @param string $field
     *
     * @return Builder
     */
    public function orWhereHas($field)
    {
        return $this->orWhere($field, Operator::$has);
    }

    /**
     * Adds a 'where not has' clause to the current query.
     *
     * @param string $field
     *
     * @return Builder
     */
    public function orWhereNotHas($field)
    {
        return $this->orWhere($field, Operator::$notHas);
    }

    /**
     * Adds an 'or where equals' clause to the current query.
     *
     * @param string $field
     * @param string $value
     *
     * @return Builder
     */
    public function orWhereEquals($field, $value)
    {
        return $this->orWhere($field, Operator::$equals, $value);
    }

    /**
     * Adds an 'or where not equals' clause to the current query.
     *
     * @param string $field
     * @param string $value
     *
     * @return Builder
     */
    public function orWhereNotEquals($field, $value)
    {
        return $this->orWhere($field, Operator::$doesNotEqual, $value);
    }

    /**
     * Adds a 'or where approximately equals' clause to the current query.
     *
     * @param string $field
     * @param string $value
     *
     * @return Builder
     */
    public function orWhereApproximatelyEquals($field, $value)
    {
        return $this->orWhere($field, Operator::$approximatelyEquals, $value);
    }

    /**
     * Adds an 'or where contains' clause to the current query.
     *
     * @param string $field
     * @param string $value
     *
     * @return Builder
     */
    public function orWhereContains($field, $value)
    {
        return $this->orWhere($field, Operator::$contains, $value);
    }

    /**
     * Adds an 'or where *not* contains' clause to the current query.
     *
     * @param string $field
     * @param string $value
     *
     * @return Builder
     */
    public function orWhereNotContains($field, $value)
    {
        return $this->orWhere($field, Operator::$notContains, $value);
    }

    /**
     * Adds an 'or where starts with' clause to the current query.
     *
     * @param string $field
     * @param string $value
     *
     * @return Builder
     */
    public function orWhereStartsWith($field, $value)
    {
        return $this->orWhere($field, Operator::$startsWith, $value);
    }

    /**
     * Adds an 'or where *not* starts with' clause to the current query.
     *
     * @param string $field
     * @param string $value
     *
     * @return Builder
     */
    public function orWhereNotStartsWith($field, $value)
    {
        return $this->orWhere($field, Operator::$notStartsWith, $value);
    }

    /**
     * Adds an 'or where ends with' clause to the current query.
     *
     * @param string $field
     * @param string $value
     *
     * @return Builder
     */
    public function orWhereEndsWith($field, $value)
    {
        return $this->orWhere($field, Operator::$endsWith, $value);
    }

    /**
     * Adds an 'or where *not* ends with' clause to the current query.
     *
     * @param string $field
     * @param string $value
     *
     * @return Builder
     */
    public function orWhereNotEndsWith($field, $value)
    {
        return $this->orWhere($field, Operator::$notEndsWith, $value);
    }

    /**
     * Adds an 'or where member of' filter to the current query.
     *
     * @param string $dn
     *
     * @return Builder
     */
    public function orWhereMemberOf($dn)
    {
        return $this->orWhereEquals($this->schema->memberOfRecursive(), $dn);
    }

    /**
     * Adds a filter onto the current query.
     *
     * @param string $type     The type of filter to add.
     * @param array  $bindings The bindings of the filter.
     *
     * @throws InvalidArgumentException
     *
     * @return $this
     */
    public function addFilter($type, array $bindings)
    {
        // Here we will ensure we have been given a proper filter type.
        if (!array_key_exists($type, $this->filters)) {
            throw new InvalidArgumentException("Invalid filter type: {$type}.");
        }

        // The required filter key bindings.
        $required = ['field', 'operator', 'value'];

        // Here we will ensure the proper key bindings are given.
        if (count(array_intersect_key(array_flip($required), $bindings)) !== count($required)) {
            // Retrieve the keys that are missing in the bindings array.
            $missing = implode(', ', array_diff($required, array_flip($bindings)));

            throw new InvalidArgumentException("Invalid filter bindings. Missing: {$missing} keys.");
        }

        $this->filters[$type][] = $bindings;

        return $this;
    }

    /**
     * Clear the query builders filters.
     *
     * @return $this
     */
    public function clearFilters()
    {
        foreach ($this->filters as $type => $filters) {
            $this->filters[$type] = [];
        }

        return $this;
    }

    /**
     * Returns true / false depending if the current object
     * contains selects.
     *
     * @return bool
     */
    public function hasSelects()
    {
        return count($this->getSelects()) > 0;
    }

    /**
     * Returns the current selected fields to retrieve.
     *
     * @return array
     */
    public function getSelects()
    {
        $selects = $this->columns;

        // If the asterisk is not provided in the selected columns, we need to
        // ensure we always select the object class and category, as these
        // are used for constructing models. The asterisk indicates that
        // we want all attributes returned for LDAP records.
        if (!in_array('*', $selects)) {
            $selects[] = $this->schema->objectCategory();
            $selects[] = $this->schema->objectClass();
        }

        return $selects;
    }

    /**
     * Sorts the LDAP search results by the specified field and direction.
     *
     * @param string   $field
     * @param string   $direction
     * @param int|null $flags
     *
     * @return Builder
     */
    public function sortBy($field, $direction = 'asc', $flags = null)
    {
        $this->sortByField = $field;

        // Normalize direction.
        $direction = strtolower($direction);

        if ($direction === 'asc' || $direction === 'desc') {
            $this->sortByDirection = $direction;
        }

        if (is_null($flags)) {
            $this->sortByFlags = SORT_NATURAL + SORT_FLAG_CASE;
        }

        return $this;
    }

    /**
     * Set the query to search on the base distinguished name.
     *
     * This will result in one record being returned.
     *
     * @return Builder
     */
    public function read()
    {
        $this->type = 'read';

        return $this;
    }

    /**
     * Set the query to search one level on the base distinguished name.
     *
     * @return Builder
     */
    public function listing()
    {
        $this->type = 'listing';

        return $this;
    }

    /**
     * Sets the query to search the entire directory on the base distinguished name.
     *
     * @return Builder
     */
    public function recursive()
    {
        $this->type = 'search';

        return $this;
    }

    /**
     * Whether to return the LDAP results in their raw format.
     *
     * @param bool $raw
     *
     * @return Builder
     */
    public function raw($raw = true)
    {
        $this->raw = (bool) $raw;

        return $this;
    }

    /**
     * Whether the current query is nested.
     *
     * @param bool $nested
     *
     * @return Builder
     */
    public function nested($nested = true)
    {
        $this->nested = (bool) $nested;

        return $this;
    }

    /**
     * Enables caching on the current query until the given date.
     *
     * If flushing is enabled, the query cache will be flushed and then re-cached.
     *
     * @param \DateTimeInterface $until When to expire the query cache.
     * @param bool               $flush Whether to force-flush the query cache.
     *
     * @return $this
     */
    public function cache(\DateTimeInterface $until = null, $flush = false)
    {
        $this->caching = true;
        $this->cacheUntil = $until;
        $this->flushCache = $flush;

        return $this;
    }

    /**
     * Returns an escaped string for use in an LDAP filter.
     *
     * @param string $value
     * @param string $ignore
     * @param int    $flags
     *
     * @return string
     */
    public function escape($value, $ignore = '', $flags = 0)
    {
        return ldap_escape($value, $ignore, $flags);
    }

    /**
     * Returns the query builders sort by field.
     *
     * @return string
     */
    public function getSortByField()
    {
        return $this->sortByField;
    }

    /**
     * Returns the query builders sort by direction.
     *
     * @return string
     */
    public function getSortByDirection()
    {
        return $this->sortByDirection;
    }

    /**
     * Returns the query builders sort by flags.
     *
     * @return int
     */
    public function getSortByFlags()
    {
        return $this->sortByFlags;
    }

    /**
     * Returns true / false if the current query is nested.
     *
     * @return bool
     */
    public function isNested()
    {
        return $this->nested === true;
    }

    /**
     * Returns bool that determines whether the current
     * query builder will return raw results.
     *
     * @return bool
     */
    public function isRaw()
    {
        return $this->raw;
    }

    /**
     * Returns bool that determines whether the current
     * query builder will return paginated results.
     *
     * @return bool
     */
    public function isPaginated()
    {
        return $this->paginated;
    }

    /**
     * Returns bool that determines whether the current
     * query builder will return sorted results.
     *
     * @return bool
     */
    public function isSorted()
    {
        return $this->sortByField ? true : false;
    }

    /**
     * Handle dynamic method calls on the query builder object to be directed to the query processor.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        // We'll check if the beginning of the method being called contains
        // 'where'. If so, we'll assume it's a dynamic 'where' clause.
        if (substr($method, 0, 5) === 'where') {
            return $this->dynamicWhere($method, $parameters);
        }

        return call_user_func_array([$this->newProcessor(), $method], $parameters);
    }

    /**
     * Handles dynamic "where" clauses to the query.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return Builder
     */
    public function dynamicWhere($method, $parameters)
    {
        $finder = substr($method, 5);

        $segments = preg_split('/(And|Or)(?=[A-Z])/', $finder, -1, PREG_SPLIT_DELIM_CAPTURE);

        // The connector variable will determine which connector will be used for the
        // query condition. We will change it as we come across new boolean values
        // in the dynamic method strings, which could contain a number of these.
        $connector = 'and';

        $index = 0;

        foreach ($segments as $segment) {
            // If the segment is not a boolean connector, we can assume it is a column's name
            // and we will add it to the query as a new constraint as a where clause, then
            // we can keep iterating through the dynamic method string's segments again.
            if ($segment != 'And' && $segment != 'Or') {
                $this->addDynamic($segment, $connector, $parameters, $index);

                $index++;
            }

            // Otherwise, we will store the connector so we know how the next where clause we
            // find in the query should be connected to the previous ones, meaning we will
            // have the proper boolean connector to connect the next where clause found.
            else {
                $connector = $segment;
            }
        }

        return $this;
    }

    /**
     * Adds an array of wheres to the current query.
     *
     * @param array  $wheres
     * @param string $boolean
     * @param bool   $raw
     *
     * @return Builder
     */
    protected function addArrayOfWheres($wheres, $boolean, $raw)
    {
        foreach ($wheres as $key => $value) {
            if (is_numeric($key) && is_array($value)) {
                // If the key is numeric and the value is an array, we'll
                // assume we've been given an array with conditionals.
                list($field, $condition) = $value;

                // Since a value is optional for some conditionals, we will
                // try and retrieve the third parameter from the array,
                // but is entirely optional.
                $value = Arr::get($value, 2);

                $this->where($field, $condition, $value, $boolean);
            } else {
                // If the value is not an array, we will assume an equals clause.
                $this->where($key, Operator::$equals, $value, $boolean, $raw);
            }
        }

        return $this;
    }

    /**
     * Add a single dynamic where clause statement to the query.
     *
     * @param string $segment
     * @param string $connector
     * @param array  $parameters
     * @param int    $index
     *
     * @return void
     */
    protected function addDynamic($segment, $connector, $parameters, $index)
    {
        // We'll format the 'where' boolean and field here to avoid casing issues.
        $bool = strtolower($connector);
        $field = strtolower($segment);

        $this->where($field, '=', $parameters[$index], $bool);
    }

    /**
     * Logs the given executed query information by firing its query event.
     *
     * @param Builder    $query
     * @param string     $type
     * @param null|float $time
     */
    protected function logQuery($query, $type, $time = null)
    {
        $args = [$query, $time];

        switch ($type) {
            case 'listing':
                $event = new Events\Listing(...$args);
                break;
            case 'read':
                $event = new Events\Read(...$args);
                break;
            case 'paginate':
                $event = new Events\Paginate(...$args);
                break;
            default:
                $event = new Events\Search(...$args);
                break;
        }

        $this->fireQueryEvent($event);
    }

    /**
     * Fires the given query event.
     *
     * @param QueryExecuted $event
     */
    protected function fireQueryEvent(QueryExecuted $event)
    {
        Adldap::getEventDispatcher()->fire($event);
    }

    /**
     * Get the elapsed time since a given starting point.
     *
     * @param int $start
     *
     * @return float
     */
    protected function getElapsedTime($start)
    {
        return round((microtime(true) - $start) * 1000, 2);
    }

    /**
     * Returns a new query Processor instance.
     *
     * @return Processor
     */
    protected function newProcessor()
    {
        return new Processor($this);
    }
}
