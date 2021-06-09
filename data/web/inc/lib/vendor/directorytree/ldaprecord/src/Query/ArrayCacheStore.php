<?php

namespace LdapRecord\Query;

use Psr\SimpleCache\CacheInterface;

class ArrayCacheStore implements CacheInterface
{
    use InteractsWithTime;

    /**
     * An array of stored values.
     *
     * @var array
     */
    protected $storage = [];

    /**
     * @inheritdoc
     */
    public function get($key, $default = null)
    {
        if (! isset($this->storage[$key])) {
            return $default;
        }

        $item = $this->storage[$key];

        $expiresAt = $item['expiresAt'] ?? 0;

        if ($expiresAt !== 0 && $this->currentTime() > $expiresAt) {
            $this->delete($key);

            return $default;
        }

        return $item['value'];
    }

    /**
     * @inheritdoc
     */
    public function set($key, $value, $ttl = null)
    {
        $this->storage[$key] = [
            'value'     => $value,
            'expiresAt' => $this->parseDateInterval($ttl),
        ];

        return true;
    }

    /**
     * @inheritdoc
     */
    public function delete($key)
    {
        unset($this->storage[$key]);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function clear()
    {
        $this->storage = [];

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getMultiple($keys, $default = null)
    {
        $values = [];

        foreach ($keys as $key) {
            $values[$key] = $this->get($key, $default);
        }

        return $values;
    }

    /**
     * @inheritdoc
     */
    public function setMultiple($values, $ttl = null)
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteMultiple($keys)
    {
        foreach ($keys as $key) {
            $this->delete($key);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function has($key)
    {
        return isset($this->storage[$key]);
    }
}
