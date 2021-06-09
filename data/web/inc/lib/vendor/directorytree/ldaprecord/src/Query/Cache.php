<?php

namespace LdapRecord\Query;

use Closure;
use DateInterval;
use DateTimeInterface;
use Psr\SimpleCache\CacheInterface;

class Cache
{
    use InteractsWithTime;

    /**
     * The cache driver.
     *
     * @var CacheInterface
     */
    protected $store;

    /**
     * Constructor.
     *
     * @param CacheInterface $store
     */
    public function __construct(CacheInterface $store)
    {
        $this->store = $store;
    }

    /**
     * Get an item from the cache.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function get($key)
    {
        return $this->store->get($key);
    }

    /**
     * Store an item in the cache.
     *
     * @param string                                  $key
     * @param mixed                                   $value
     * @param DateTimeInterface|DateInterval|int|null $ttl
     *
     * @return bool
     */
    public function put($key, $value, $ttl = null)
    {
        return $this->store->set($key, $value, $this->expiresAt($ttl));
    }

    /**
     * Get an item from the cache, or execute the given Closure and store the result.
     *
     * @param string                                  $key
     * @param DateTimeInterface|DateInterval|int|null $ttl
     * @param Closure                                 $callback
     *
     * @return mixed
     */
    public function remember($key, $ttl, Closure $callback)
    {
        $value = $this->get($key);

        if (! is_null($value)) {
            return $value;
        }

        $this->put($key, $value = $callback(), $ttl);

        return $value;
    }

    /**
     * Delete an item from the cache.
     *
     * @param string $key
     *
     * @return bool
     */
    public function delete($key)
    {
        return $this->store->delete($key);
    }

    /**
     * Get the underlying cache store.
     *
     * @return CacheInterface
     */
    public function store()
    {
        return $this->store;
    }
}
