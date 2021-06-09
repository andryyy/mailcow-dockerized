<?php

namespace LdapRecord\Models\Attributes;

use LdapRecord\EscapesValues;

class DistinguishedNameBuilder
{
    use EscapesValues;

    /**
     * The components of the DN.
     *
     * @var array
     */
    protected $components = [];

    /**
     * Whether to output the DN in reverse.
     *
     * @var bool
     */
    protected $reverse = false;

    /**
     * Constructor.
     *
     * @param string|null $value
     */
    public function __construct($dn = null)
    {
        $this->components = array_map(function ($rdn) {
            return DistinguishedName::explodeRdn($rdn);
        }, DistinguishedName::make($dn)->components());
    }

    /**
     * Forward missing method calls onto the Distinguished Name object.
     *
     * @param string $method
     * @param array  $args
     *
     * @return mixed
     */
    public function __call($method, $args)
    {
        return $this->get()->{$method}($args);
    }

    /**
     * Get the distinguished name value.
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->get();
    }

    /**
     * Prepend an RDN onto the DN.
     *
     * @param string|array $attribute
     * @param string|null  $value
     *
     * @return $this
     */
    public function prepend($attribute, $value = null)
    {
        array_unshift(
            $this->components,
            ...$this->componentize($attribute, $value)
        );

        return $this;
    }

    /**
     * Append an RDN onto the DN.
     *
     * @param string|array $attribute
     * @param string|null  $value
     *
     * @return $this
     */
    public function append($attribute, $value = null)
    {
        array_push(
            $this->components,
            ...$this->componentize($attribute, $value)
        );

        return $this;
    }

    /**
     * Componentize the attribute and value.
     *
     * @param string|array $attribute
     * @param string|null  $value
     *
     * @return array
     */
    protected function componentize($attribute, $value = null)
    {
        // Here we will make the assumption that an array of
        // RDN's have been given if the value is null, and
        // attempt to break them into their components.
        if (is_null($value)) {
            $attributes = is_array($attribute) ? $attribute : [$attribute];

            $components = array_map([$this, 'makeComponentizedArray'], $attributes);
        } else {
            $components = [[$attribute, $value]];
        }

        return array_map(function ($component) {
            [$attribute, $value] = $component;

            return $this->makeAppendableComponent($attribute, $value);
        }, $components);
    }

    /**
     * Make a componentized array by exploding the value if it's a string.
     *
     * @param string $value
     *
     * @return array
     */
    protected function makeComponentizedArray($value)
    {
        return is_array($value) ? $value : DistinguishedName::explodeRdn($value);
    }

    /**
     * Make an appendable component array from the attribute and value.
     *
     * @param string|array $attribute
     * @param string|null  $value
     *
     * @return array
     */
    protected function makeAppendableComponent($attribute, $value = null)
    {
        return [trim($attribute), $this->escape(trim($value))->dn()];
    }

    /**
     * Pop an RDN off of the end of the DN.
     *
     * @param int   $amount
     * @param array $removed
     *
     * @return $this
     */
    public function pop($amount = 1, &$removed = [])
    {
        $removed = array_map(function ($component) {
            return DistinguishedName::makeRdn($component);
        }, array_splice($this->components, -$amount, $amount));

        return $this;
    }

    /**
     * Shift an RDN off of the beginning of the DN.
     *
     * @param int   $amount
     * @param array $removed
     *
     * @return $this
     */
    public function shift($amount = 1, &$removed = [])
    {
        $removed = array_map(function ($component) {
            return DistinguishedName::makeRdn($component);
        }, array_splice($this->components, 0, $amount));

        return $this;
    }

    /**
     * Whether to output the DN in reverse.
     *
     * @return $this
     */
    public function reverse()
    {
        $this->reverse = true;

        return $this;
    }

    /**
     * Get the components of the DN.
     *
     * @param null|string $type
     *
     * @return array
     */
    public function components($type = null)
    {
        return is_null($type)
            ? $this->components
            : $this->componentsOfType($type);
    }

    /**
     * Get the components of a particular type.
     *
     * @param string $type
     *
     * @return array
     */
    protected function componentsOfType($type)
    {
        $components = array_filter($this->components, function ($component) use ($type) {
            return ([$name] = $component) && strtolower($name) === strtolower($type);
        });

        return array_values($components);
    }

    /**
     * Get the fully qualified DN.
     *
     * @return DistinguishedName
     */
    public function get()
    {
        return new DistinguishedName($this->build());
    }

    /**
     * Build the distinguished name from the components.
     *
     * @return $this
     */
    protected function build()
    {
        $components = $this->reverse
            ? array_reverse($this->components)
            : $this->components;

        return implode(',', array_map(function ($component) {
            return DistinguishedName::makeRdn($component);
        }, $components));
    }
}
