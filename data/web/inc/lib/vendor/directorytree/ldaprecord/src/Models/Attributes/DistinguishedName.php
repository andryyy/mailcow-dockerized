<?php

namespace LdapRecord\Models\Attributes;

use LdapRecord\EscapesValues;

class DistinguishedName
{
    use EscapesValues;

    /**
     * The underlying raw value.
     *
     * @var string|null
     */
    protected $value;

    /**
     * Constructor.
     *
     * @param string|null $value
     */
    public function __construct($value = null)
    {
        $this->value = trim($value);
    }

    /**
     * Get the distinguished name value.
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->value;
    }

    /**
     * Alias of the "build" method.
     *
     * @param string|null $value
     *
     * @return DistinguishedNameBuilder
     */
    public static function of($value = null)
    {
        return static::build($value);
    }

    /**
     * Get a new DN builder object from the given DN.
     *
     * @param string|null $value
     *
     * @return DistinguishedNameBuilder
     */
    public static function build($value = null)
    {
        return new DistinguishedNameBuilder($value);
    }

    /**
     * Make a new Distinguished Name instance.
     *
     * @param string|null $value
     *
     * @return static
     */
    public static function make($value = null)
    {
        return new static($value);
    }

    /**
     * Explode a distinguished name into relative distinguished names.
     *
     * @param string $dn
     *
     * @return array
     */
    public static function explode($dn)
    {
        $dn = ldap_explode_dn($dn, $withoutAttributes = false);

        if (! is_array($dn)) {
            return [];
        }

        if (! array_key_exists('count', $dn)) {
            return [];
        }

        unset($dn['count']);

        return $dn;
    }

    /**
     * Un-escapes a hexadecimal string into its original string representation.
     *
     * @param string $value
     *
     * @return string
     */
    public static function unescape($value)
    {
        return preg_replace_callback('/\\\([0-9A-Fa-f]{2})/', function ($matches) {
            return chr(hexdec($matches[1]));
        }, $value);
    }

    /**
     * Explode the RDN into an attribute and value.
     *
     * @param string $rdn
     *
     * @return array
     */
    public static function explodeRdn($rdn)
    {
        return explode('=', $rdn, $limit = 2);
    }

    /**
     * Implode the component attribute and value into an RDN.
     *
     * @param string $rdn
     *
     * @return string
     */
    public static function makeRdn(array $component)
    {
        return implode('=', $component);
    }

    /**
     * Get the underlying value.
     *
     * @return string|null
     */
    public function get()
    {
        return $this->value;
    }

    /**
     * Set the underlying value.
     *
     * @param string|null $value
     *
     * @return $this
     */
    public function set($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get the Distinguished Name values without attributes.
     *
     * @return array
     */
    public function values()
    {
        $components = $this->components();

        $values = [];

        foreach ($components as $rdn) {
            [,$value] = static::explodeRdn($rdn);

            $values[] = static::unescape($value);
        }

        return $values;
    }

    /**
     * Get the Distinguished Name components with attributes.
     *
     * @return array
     */
    public function components()
    {
        $rdns = static::explode($this->value);

        $components = [];

        foreach ($rdns as $rdn) {
            [$attribute, $value] = static::explodeRdn($rdn);

            // When a Distinguished Name is exploded, the values are automatically
            // escaped. This cannot be opted out of. Here we will unescape
            // the attribute value, then re-escape it to its original
            // representation from the server using the "dn" flag.
            $value = $this->escape(static::unescape($value))->dn();

            $components[] = static::makeRdn([
                $attribute, $value,
            ]);
        }

        return $components;
    }

    /**
     * Convert the DN into an associative array.
     *
     * @return array
     */
    public function assoc()
    {
        $map = [];

        foreach ($this->components() as $rdn) {
            [$attribute, $value] = static::explodeRdn($rdn);

            $attribute = $this->normalize($attribute);

            array_key_exists($attribute, $map)
                ? $map[$attribute][] = $value
                : $map[$attribute] = [$value];
        }

        return $map;
    }

    /**
     * Get the name value.
     *
     * @return string|null
     */
    public function name()
    {
        $values = $this->values();

        return reset($values) ?: null;
    }

    /**
     * Get the relative Distinguished name.
     *
     * @return string|null
     */
    public function relative()
    {
        $components = $this->components();

        return reset($components) ?: null;
    }

    /**
     * Get the parent Distinguished name.
     *
     * @return string|null
     */
    public function parent()
    {
        $components = $this->components();

        array_shift($components);

        return implode(',', $components) ?: null;
    }

    /**
     * Determine if the current Distinguished Name is a parent of the given child.
     *
     * @param DistinguishedName $child
     *
     * @return bool
     */
    public function isParentOf(self $child)
    {
        return $child->isChildOf($this);
    }

    /**
     * Determine if the current Distinguished Name is a child of the given parent.
     *
     * @param DistinguishedName $parent
     *
     * @return bool
     */
    public function isChildOf(self $parent)
    {
        if (
            empty($components = $this->components()) ||
            empty($parentComponents = $parent->components())
        ) {
            return false;
        }

        array_shift($components);

        return $this->compare($components, $parentComponents);
    }

    /**
     * Determine if the current Distinguished Name is an ancestor of the descendant.
     *
     * @param DistinguishedName $descendant
     *
     * @return bool
     */
    public function isAncestorOf(self $descendant)
    {
        return $descendant->isDescendantOf($this);
    }

    /**
     * Determine if the current Distinguished Name is a descendant of the ancestor.
     *
     * @param DistinguishedName $ancestor
     *
     * @return bool
     */
    public function isDescendantOf(self $ancestor)
    {
        if (
            empty($components = $this->components()) ||
            empty($ancestorComponents = $ancestor->components())
        ) {
            return false;
        }

        if (! $length = count($components) - count($ancestorComponents)) {
            return false;
        }

        array_splice($components, $offset = 0, $length);

        return $this->compare($components, $ancestorComponents);
    }

    /**
     * Compare whether the two Distinguished Name values are equal.
     *
     * @param array $values
     * @param array $other
     *
     * @return bool
     */
    protected function compare(array $values, array $other)
    {
        return $this->recase($values) == $this->recase($other);
    }

    /**
     * Recase the array values.
     *
     * @param array $values
     *
     * @return array
     */
    protected function recase(array $values)
    {
        return array_map([$this, 'normalize'], $values);
    }

    /**
     * Normalize the string value.
     *
     * @param string $value
     *
     * @return string
     */
    protected function normalize($value)
    {
        return strtolower($value);
    }
}
