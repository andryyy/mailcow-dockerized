<?php

namespace Adldap\Connections;

/**
 * Class Ldap.
 *
 * A class that abstracts PHP's LDAP functions and stores the bound connection.
 */
class Ldap implements ConnectionInterface
{
    /**
     * The connection name.
     *
     * @var string|null
     */
    protected $name;

    /**
     * The LDAP host that is currently connected.
     *
     * @var string|null
     */
    protected $host;

    /**
     * The active LDAP connection.
     *
     * @var resource
     */
    protected $connection;

    /**
     * The bound status of the connection.
     *
     * @var bool
     */
    protected $bound = false;

    /**
     * Whether the connection must be bound over SSL.
     *
     * @var bool
     */
    protected $useSSL = false;

    /**
     * Whether the connection must be bound over TLS.
     *
     * @var bool
     */
    protected $useTLS = false;

    /**
     * {@inheritdoc}
     */
    public function __construct($name = null)
    {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function isUsingSSL()
    {
        return $this->useSSL;
    }

    /**
     * {@inheritdoc}
     */
    public function isUsingTLS()
    {
        return $this->useTLS;
    }

    /**
     * {@inheritdoc}
     */
    public function isBound()
    {
        return $this->bound;
    }

    /**
     * {@inheritdoc}
     */
    public function canChangePasswords()
    {
        return $this->isUsingSSL() || $this->isUsingTLS();
    }

    /**
     * {@inheritdoc}
     */
    public function ssl($enabled = true)
    {
        $this->useSSL = $enabled;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function tls($enabled = true)
    {
        $this->useTLS = $enabled;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
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
    public function getEntries($searchResults)
    {
        return ldap_get_entries($this->connection, $searchResults);
    }

    /**
     * {@inheritdoc}
     */
    public function getFirstEntry($searchResults)
    {
        return ldap_first_entry($this->connection, $searchResults);
    }

    /**
     * {@inheritdoc}
     */
    public function getNextEntry($entry)
    {
        return ldap_next_entry($this->connection, $entry);
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes($entry)
    {
        return ldap_get_attributes($this->connection, $entry);
    }

    /**
     * {@inheritdoc}
     */
    public function countEntries($searchResults)
    {
        return ldap_count_entries($this->connection, $searchResults);
    }

    /**
     * {@inheritdoc}
     */
    public function compare($dn, $attribute, $value)
    {
        return ldap_compare($this->connection, $dn, $attribute, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getLastError()
    {
        return ldap_error($this->connection);
    }

    /**
     * {@inheritdoc}
     */
    public function getDetailedError()
    {
        // If the returned error number is zero, the last LDAP operation
        // succeeded. We won't return a detailed error.
        if ($number = $this->errNo()) {
            ldap_get_option($this->connection, LDAP_OPT_DIAGNOSTIC_MESSAGE, $message);

            return new DetailedError($number, $this->err2Str($number), $message);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getValuesLen($entry, $attribute)
    {
        return ldap_get_values_len($this->connection, $entry, $attribute);
    }

    /**
     * {@inheritdoc}
     */
    public function setOption($option, $value)
    {
        return ldap_set_option($this->connection, $option, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function setOptions(array $options = [])
    {
        foreach ($options as $option => $value) {
            $this->setOption($option, $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setRebindCallback(callable $callback)
    {
        return ldap_set_rebind_proc($this->connection, $callback);
    }

    /**
     * {@inheritdoc}
     */
    public function startTLS()
    {
        try {
            return ldap_start_tls($this->connection);
        } catch (\ErrorException $e) {
            throw new ConnectionException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function connect($hosts = [], $port = 389)
    {
        $this->host = $this->getConnectionString($hosts, $this->getProtocol(), $port);

        // Reset the bound status if reinitializing the connection.
        $this->bound = false;

        return $this->connection = ldap_connect($this->host);
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        $connection = $this->connection;

        $result = is_resource($connection) ? ldap_close($connection) : false;

        $this->bound = false;

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function search($dn, $filter, array $fields, $onlyAttributes = false, $size = 0, $time = 0)
    {
        return ldap_search($this->connection, $dn, $filter, $fields, $onlyAttributes, $size, $time);
    }

    /**
     * {@inheritdoc}
     */
    public function listing($dn, $filter, array $fields, $onlyAttributes = false, $size = 0, $time = 0)
    {
        return ldap_list($this->connection, $dn, $filter, $fields, $onlyAttributes, $size, $time);
    }

    /**
     * {@inheritdoc}
     */
    public function read($dn, $filter, array $fields, $onlyAttributes = false, $size = 0, $time = 0)
    {
        return ldap_read($this->connection, $dn, $filter, $fields, $onlyAttributes, $size, $time);
    }

    /**
     * Extract information from an LDAP result.
     *
     * @link https://www.php.net/manual/en/function.ldap-parse-result.php
     *
     * @param resource $result
     * @param int      $errorCode
     * @param string   $dn
     * @param string   $errorMessage
     * @param array    $referrals
     * @param array    $serverControls
     *
     * @return bool
     */
    public function parseResult($result, &$errorCode, &$dn, &$errorMessage, &$referrals, &$serverControls = [])
    {
        return $this->supportsServerControlsInMethods() && !empty($serverControls) ?
            ldap_parse_result($this->connection, $result, $errorCode, $dn, $errorMessage, $referrals, $serverControls) :
            ldap_parse_result($this->connection, $result, $errorCode, $dn, $errorMessage, $referrals);
    }

    /**
     * {@inheritdoc}
     */
    public function bind($username, $password, $sasl = false)
    {
        // Prior to binding, we will upgrade our connectivity to TLS on our current
        // connection and ensure we are not already bound before upgrading.
        // This is to prevent subsequent upgrading on several binds.
        if ($this->isUsingTLS() && !$this->isBound()) {
            $this->startTLS();
        }

        if ($sasl) {
            return $this->bound = ldap_sasl_bind($this->connection, null, null, 'GSSAPI');
        }

        return $this->bound = ldap_bind(
            $this->connection,
            $username,
            html_entity_decode($password)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function add($dn, array $entry)
    {
        return ldap_add($this->connection, $dn, $entry);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($dn)
    {
        return ldap_delete($this->connection, $dn);
    }

    /**
     * {@inheritdoc}
     */
    public function rename($dn, $newRdn, $newParent, $deleteOldRdn = false)
    {
        return ldap_rename($this->connection, $dn, $newRdn, $newParent, $deleteOldRdn);
    }

    /**
     * {@inheritdoc}
     */
    public function modify($dn, array $entry)
    {
        return ldap_modify($this->connection, $dn, $entry);
    }

    /**
     * {@inheritdoc}
     */
    public function modifyBatch($dn, array $values)
    {
        return ldap_modify_batch($this->connection, $dn, $values);
    }

    /**
     * {@inheritdoc}
     */
    public function modAdd($dn, array $entry)
    {
        return ldap_mod_add($this->connection, $dn, $entry);
    }

    /**
     * {@inheritdoc}
     */
    public function modReplace($dn, array $entry)
    {
        return ldap_mod_replace($this->connection, $dn, $entry);
    }

    /**
     * {@inheritdoc}
     */
    public function modDelete($dn, array $entry)
    {
        return ldap_mod_del($this->connection, $dn, $entry);
    }

    /**
     * {@inheritdoc}
     */
    public function controlPagedResult($pageSize = 1000, $isCritical = false, $cookie = '')
    {
        return ldap_control_paged_result($this->connection, $pageSize, $isCritical, $cookie);
    }

    /**
     * {@inheritdoc}
     */
    public function controlPagedResultResponse($result, &$cookie)
    {
        return ldap_control_paged_result_response($this->connection, $result, $cookie);
    }

    /**
     * {@inheritdoc}
     */
    public function freeResult($result)
    {
        return ldap_free_result($result);
    }

    /**
     * {@inheritdoc}
     */
    public function errNo()
    {
        return ldap_errno($this->connection);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedError()
    {
        return $this->getDiagnosticMessage();
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedErrorHex()
    {
        if (preg_match("/(?<=data\s).*?(?=\,)/", $this->getExtendedError(), $code)) {
            return $code[0];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedErrorCode()
    {
        return $this->extractDiagnosticCode($this->getExtendedError());
    }

    /**
     * {@inheritdoc}
     */
    public function err2Str($number)
    {
        return ldap_err2str($number);
    }

    /**
     * {@inheritdoc}
     */
    public function getDiagnosticMessage()
    {
        ldap_get_option($this->connection, LDAP_OPT_ERROR_STRING, $message);

        return $message;
    }

    /**
     * {@inheritdoc}
     */
    public function extractDiagnosticCode($message)
    {
        preg_match('/^([\da-fA-F]+):/', $message, $matches);

        return isset($matches[1]) ? $matches[1] : false;
    }

    /**
     * Returns the LDAP protocol to utilize for the current connection.
     *
     * @return string
     */
    public function getProtocol()
    {
        return $this->isUsingSSL() ? $this::PROTOCOL_SSL : $this::PROTOCOL;
    }

    /**
     * Determine if the current PHP version supports server controls.
     *
     * @return bool
     */
    public function supportsServerControlsInMethods()
    {
        return version_compare(PHP_VERSION, '7.3.0') >= 0;
    }

    /**
     * Generates an LDAP connection string for each host given.
     *
     * @param string|array $hosts
     * @param string       $protocol
     * @param string       $port
     *
     * @return string
     */
    protected function getConnectionString($hosts, $protocol, $port)
    {
        // If we are using SSL and using the default port, we
        // will override it to use the default SSL port.
        if ($this->isUsingSSL() && $port == 389) {
            $port = self::PORT_SSL;
        }

        // Normalize hosts into an array.
        $hosts = is_array($hosts) ? $hosts : [$hosts];

        $hosts = array_map(function ($host) use ($protocol, $port) {
            return "{$protocol}{$host}:{$port}";
        }, $hosts);

        return implode(' ', $hosts);
    }
}
