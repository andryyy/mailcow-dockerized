<?php

namespace Adldap\Models\Concerns;

use Illuminate\Support\Arr;

trait HasAttributes
{
    /**
     * The default output date format for all time related methods.
     *
     * Default format is suited for MySQL timestamps.
     *
     * @var string
     */
    public $dateFormat = 'Y-m-d H:i:s';

    /**
     * The format that is used to convert timestamps to unix timestamps.
     *
     * Currently set for compatibility with Active Directory.
     *
     * @var string
     */
    protected $timestampFormat = 'YmdHis.0Z';

    /**
     * The models attributes.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * The models original attributes.
     *
     * @var array
     */
    protected $original = [];

    /**
     * Dynamically retrieve attributes on the object.
     *
     * @param mixed $key
     *
     * @return bool
     */
    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    /**
     * Dynamically set attributes on the object.
     *
     * @param mixed $key
     * @param mixed $value
     *
     * @return $this
     */
    public function __set($key, $value)
    {
        return $this->setAttribute($key, $value);
    }

    /**
     * Synchronizes the models original attributes
     * with the model's current attributes.
     *
     * @return $this
     */
    public function syncOriginal()
    {
        $this->original = $this->attributes;

        return $this;
    }

    /**
     * Returns the models attribute with the specified key.
     *
     * If a sub-key is specified, it will try and
     * retrieve it from the parent keys array.
     *
     * @param int|string $key
     * @param int|string $subKey
     *
     * @return mixed
     */
    public function getAttribute($key, $subKey = null)
    {
        if (!$key) {
            return;
        }

        // We'll normalize the given key to prevent case sensitivity issues.
        $key = $this->normalizeAttributeKey($key);

        if (is_null($subKey) && $this->hasAttribute($key)) {
            return $this->attributes[$key];
        } elseif ($this->hasAttribute($key, $subKey)) {
            return $this->attributes[$key][$subKey];
        }
    }

    /**
     * Returns the first attribute by the specified key.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getFirstAttribute($key)
    {
        return $this->getAttribute($key, 0);
    }

    /**
     * Returns all of the models attributes.
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Fills the entry with the supplied attributes.
     *
     * @param array $attributes
     *
     * @return $this
     */
    public function fill(array $attributes = [])
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }

        return $this;
    }

    /**
     * Sets an attributes value by the specified key and sub-key.
     *
     * @param int|string $key
     * @param mixed      $value
     * @param int|string $subKey
     *
     * @return $this
     */
    public function setAttribute($key, $value, $subKey = null)
    {
        // Normalize key.
        $key = $this->normalizeAttributeKey($key);

        // If the key is equal to 'dn', we'll automatically
        // change it to the full attribute name.
        $key = ($key == 'dn' ? $this->schema->distinguishedName() : $key);

        if (is_null($subKey)) {
            // We need to ensure all attributes are set as arrays so all
            // of our model methods retrieve attributes correctly.
            $this->attributes[$key] = is_array($value) ? $value : [$value];
        } else {
            $this->attributes[$key][$subKey] = $value;
        }

        return $this;
    }

    /**
     * Sets the first attributes value by the specified key.
     *
     * @param int|string $key
     * @param mixed      $value
     *
     * @return $this
     */
    public function setFirstAttribute($key, $value)
    {
        return $this->setAttribute($key, $value, 0);
    }

    /**
     * Sets the attributes property.
     *
     * Used when constructing an existing LDAP record.
     *
     * @param array $attributes
     *
     * @return $this
     */
    public function setRawAttributes(array $attributes = [])
    {
        // We'll filter out those annoying 'count' keys returned with LDAP results,
        // and lowercase all root array keys to prevent any casing issues.
        $this->attributes = array_change_key_case($this->filterRawAttributes($attributes), CASE_LOWER);

        // We'll pull out the distinguished name from our raw attributes
        // and set it into our attributes array with the full attribute
        // definition. This allows us to normalize distinguished
        // names across different LDAP variants.
        if ($dn = Arr::get($attributes, 'dn')) {
            // In some LDAP variants, the distinguished
            // name is returned as an array.
            if (is_array($dn)) {
                $dn = Arr::first($dn);
            }

            $this->setDistinguishedName($dn);
        }

        $this->syncOriginal();

        // Set exists to true since raw attributes are only
        // set in the case of attributes being loaded by
        // query results.
        $this->exists = true;

        return $this;
    }

    /**
     * Filters the count key recursively from raw LDAP attributes.
     *
     * @param array        $attributes
     * @param array|string $keys
     *
     * @return array
     */
    public function filterRawAttributes(array $attributes = [], $keys = ['count', 'dn'])
    {
        $attributes = Arr::except($attributes, $keys);

        array_walk($attributes, function (&$value) use ($keys) {
            $value = is_array($value) ?
                $this->filterRawAttributes($value, $keys) :
                $value;
        });

        return $attributes;
    }

    /**
     * Returns true / false if the specified attribute
     * exists in the attributes array.
     *
     * @param int|string $key
     * @param int|string $subKey
     *
     * @return bool
     */
    public function hasAttribute($key, $subKey = null)
    {
        // Normalize key.
        $key = $this->normalizeAttributeKey($key);

        if (is_null($subKey)) {
            return Arr::has($this->attributes, $key);
        }

        return Arr::has($this->attributes, "$key.$subKey");
    }

    /**
     * Returns the number of attributes inside
     * the attributes property.
     *
     * @return int
     */
    public function countAttributes()
    {
        return count($this->getAttributes());
    }

    /**
     * Returns the models original attributes.
     *
     * @return array
     */
    public function getOriginal()
    {
        return $this->original;
    }

    /**
     * Get the attributes that have been changed since last sync.
     *
     * @return array
     */
    public function getDirty()
    {
        $dirty = [];

        foreach ($this->attributes as $key => $value) {
            if (!$this->originalIsEquivalent($key)) {
                // We need to reset the array's indices using array_values due to
                // LDAP requiring consecutive indices (0, 1, 2 etc.)
                $dirty[$key] = array_values($value);
            }
        }

        return $dirty;
    }

    /**
     * Returns a normalized attribute key.
     *
     * @param string $key
     *
     * @return string
     */
    protected function normalizeAttributeKey($key)
    {
        return strtolower($key);
    }

    /**
     * Determine if the new and old values for a given key are equivalent.
     *
     * @param string $key
     *
     * @return bool
     */
    protected function originalIsEquivalent($key)
    {
        if (!array_key_exists($key, $this->original)) {
            return false;
        }

        $current = $this->attributes[$key];

        $original = $this->original[$key];

        if ($current === $original) {
            return true;
        }

        return  is_numeric($current) &&
                is_numeric($original) &&
                strcmp((string) $current, (string) $original) === 0;
    }
}
