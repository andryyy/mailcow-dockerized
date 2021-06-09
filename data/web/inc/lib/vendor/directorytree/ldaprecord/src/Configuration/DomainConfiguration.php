<?php

namespace LdapRecord\Configuration;

use LdapRecord\LdapInterface;

class DomainConfiguration
{
    /**
     * The extended configuration options.
     *
     * @var array
     */
    protected static $extended = [];

    /**
     * The configuration options array.
     *
     * The default values for each key indicate the type of value it requires.
     *
     * @var array
     */
    protected $options = [
        // An array of LDAP hosts.
        'hosts' => [],

        // The global LDAP operation timeout limit in seconds.
        'timeout' => 5,

        // The LDAP version to utilize.
        'version' => 3,

        // The port to use for connecting to your hosts.
        'port' => LdapInterface::PORT,

        // The base distinguished name of your domain.
        'base_dn' => '',

        // The username to use for binding.
        'username' => '',

        // The password to use for binding.
        'password' => '',

        // Whether or not to use SSL when connecting.
        'use_ssl' => false,

        // Whether or not to use TLS when connecting.
        'use_tls' => false,

        // Whether or not follow referrals is enabled when performing LDAP operations.
        'follow_referrals' => false,

        // Custom LDAP options.
        'options' => [],
    ];

    /**
     * Constructor.
     *
     * @param array $options
     *
     * @throws ConfigurationException When an option value given is an invalid type.
     */
    public function __construct(array $options = [])
    {
        $this->options = array_merge($this->options, static::$extended);

        foreach ($options as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * Extend the configuration with a custom option, or override an existing.
     *
     * @param string $option
     * @param mixed  $default
     *
     * @return void
     */
    public static function extend($option, $default = null)
    {
        static::$extended[$option] = $default;
    }

    /**
     * Flush the extended configuration options.
     *
     * @return void
     */
    public static function flushExtended()
    {
        static::$extended = [];
    }

    /**
     * Get all configuration options.
     *
     * @return array
     */
    public function all()
    {
        return $this->options;
    }

    /**
     * Set a configuration option.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @throws ConfigurationException When an option value given is an invalid type.
     */
    public function set($key, $value)
    {
        if ($this->validate($key, $value)) {
            $this->options[$key] = $value;
        }
    }

    /**
     * Returns the value for the specified configuration options.
     *
     * @param string $key
     *
     * @return mixed
     *
     * @throws ConfigurationException When the option specified does not exist.
     */
    public function get($key)
    {
        if (! $this->has($key)) {
            throw new ConfigurationException("Option {$key} does not exist.");
        }

        return $this->options[$key];
    }

    /**
     * Checks if a configuration option exists.
     *
     * @param string $key
     *
     * @return bool
     */
    public function has($key)
    {
        return array_key_exists($key, $this->options);
    }

    /**
     * Validate the configuration option.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return bool
     *
     * @throws ConfigurationException When an option value given is an invalid type.
     */
    protected function validate($key, $value)
    {
        $default = $this->get($key);

        if (is_array($default)) {
            $validator = new Validators\ArrayValidator($key, $value);
        } elseif (is_int($default)) {
            $validator = new Validators\IntegerValidator($key, $value);
        } elseif (is_bool($default)) {
            $validator = new Validators\BooleanValidator($key, $value);
        } else {
            $validator = new Validators\StringOrNullValidator($key, $value);
        }

        return $validator->validate();
    }
}
