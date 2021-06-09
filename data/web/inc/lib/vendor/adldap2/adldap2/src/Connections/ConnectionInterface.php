<?php

namespace Adldap\Connections;

/**
 * The Connection interface used for making connections. Implementing
 * this interface on connection classes helps unit and functional
 * test classes that require a connection.
 *
 * Interface ConnectionInterface
 */
interface ConnectionInterface
{
    /**
     * The SSL LDAP protocol string.
     *
     * @var string
     */
    const PROTOCOL_SSL = 'ldaps://';

    /**
     * The standard LDAP protocol string.
     *
     * @var string
     */
    const PROTOCOL = 'ldap://';

    /**
     * The LDAP SSL port number.
     *
     * @var string
     */
    const PORT_SSL = 636;

    /**
     * The standard LDAP port number.
     *
     * @var string
     */
    const PORT = 389;

    /**
     * Constructor.
     *
     * @param string|null $name The connection name.
     */
    public function __construct($name = null);

    /**
     * Returns true / false if the current connection instance is using SSL.
     *
     * @return bool
     */
    public function isUsingSSL();

    /**
     * Returns true / false if the current connection instance is using TLS.
     *
     * @return bool
     */
    public function isUsingTLS();

    /**
     * Returns true / false if the current connection is able to modify passwords.
     *
     * @return bool
     */
    public function canChangePasswords();

    /**
     * Returns true / false if the current connection is bound.
     *
     * @return bool
     */
    public function isBound();

    /**
     * Sets the current connection to use SSL.
     *
     * @param bool $enabled
     *
     * @return ConnectionInterface
     */
    public function ssl($enabled = true);

    /**
     * Sets the current connection to use TLS.
     *
     * @param bool $enabled
     *
     * @return ConnectionInterface
     */
    public function tls($enabled = true);

    /**
     * Returns the full LDAP host URL.
     *
     * Ex: ldap://192.168.1.1:386
     *
     * @return string|null
     */
    public function getHost();

    /**
     * Returns the connections name.
     *
     * @return string|null
     */
    public function getName();

    /**
     * Get the current connection.
     *
     * @return mixed
     */
    public function getConnection();

    /**
     * Retrieve the entries from a search result.
     *
     * @link http://php.net/manual/en/function.ldap-get-entries.php
     *
     * @param $searchResult
     *
     * @return mixed
     */
    public function getEntries($searchResult);

    /**
     * Returns the number of entries from a search result.
     *
     * @link http://php.net/manual/en/function.ldap-count-entries.php
     *
     * @param $searchResult
     *
     * @return int
     */
    public function countEntries($searchResult);

    /**
     * Compare value of attribute found in entry specified with DN.
     *
     * @link http://php.net/manual/en/function.ldap-compare.php
     *
     * @param string $dn
     * @param string $attribute
     * @param string $value
     *
     * @return mixed
     */
    public function compare($dn, $attribute, $value);

    /**
     * Retrieves the first entry from a search result.
     *
     * @link http://php.net/manual/en/function.ldap-first-entry.php
     *
     * @param $searchResult
     *
     * @return mixed
     */
    public function getFirstEntry($searchResult);

    /**
     * Retrieves the next entry from a search result.
     *
     * @link http://php.net/manual/en/function.ldap-next-entry.php
     *
     * @param $entry
     *
     * @return mixed
     */
    public function getNextEntry($entry);

    /**
     * Retrieves the ldap entry's attributes.
     *
     * @link http://php.net/manual/en/function.ldap-get-attributes.php
     *
     * @param $entry
     *
     * @return mixed
     */
    public function getAttributes($entry);

    /**
     * Retrieve the last error on the current connection.
     *
     * @link http://php.net/manual/en/function.ldap-error.php
     *
     * @return string
     */
    public function getLastError();

    /**
     * Return detailed information about an error.
     *
     * Returns false when there was a successful last request.
     *
     * Returns DetailedError when there was an error.
     *
     * @return DetailedError|null
     */
    public function getDetailedError();

    /**
     * Get all binary values from the specified result entry.
     *
     * @link http://php.net/manual/en/function.ldap-get-values-len.php
     *
     * @param $entry
     * @param $attribute
     *
     * @return array
     */
    public function getValuesLen($entry, $attribute);

    /**
     * Sets an option on the current connection.
     *
     * @link http://php.net/manual/en/function.ldap-set-option.php
     *
     * @param int   $option
     * @param mixed $value
     *
     * @return mixed
     */
    public function setOption($option, $value);

    /**
     * Sets options on the current connection.
     *
     * @param array $options
     *
     * @return mixed
     */
    public function setOptions(array $options = []);

    /**
     * Set a callback function to do re-binds on referral chasing.
     *
     * @link http://php.net/manual/en/function.ldap-set-rebind-proc.php
     *
     * @param callable $callback
     *
     * @return bool
     */
    public function setRebindCallback(callable $callback);

    /**
     * Connects to the specified hostname using the specified port.
     *
     * @link http://php.net/manual/en/function.ldap-start-tls.php
     *
     * @param string|array $hostname
     * @param int          $port
     *
     * @return mixed
     */
    public function connect($hostname = [], $port = 389);

    /**
     * Starts a connection using TLS.
     *
     * @link http://php.net/manual/en/function.ldap-start-tls.php
     *
     * @throws ConnectionException If starting TLS fails.
     *
     * @return mixed
     */
    public function startTLS();

    /**
     * Binds to the current connection using the specified username and password.
     * If sasl is true, the current connection is bound using SASL.
     *
     * @link http://php.net/manual/en/function.ldap-bind.php
     *
     * @param string $username
     * @param string $password
     * @param bool   $sasl
     *
     * @throws ConnectionException If starting TLS fails.
     *
     * @return bool
     */
    public function bind($username, $password, $sasl = false);

    /**
     * Closes the current connection.
     *
     * Returns false if no connection is present.
     *
     * @link http://php.net/manual/en/function.ldap-close.php
     *
     * @return bool
     */
    public function close();

    /**
     * Performs a search on the current connection.
     *
     * @link http://php.net/manual/en/function.ldap-search.php
     *
     * @param string $dn
     * @param string $filter
     * @param array  $fields
     * @param bool   $onlyAttributes
     * @param int    $size
     * @param int    $time
     *
     * @return mixed
     */
    public function search($dn, $filter, array $fields, $onlyAttributes = false, $size = 0, $time = 0);

    /**
     * Reads an entry on the current connection.
     *
     * @link http://php.net/manual/en/function.ldap-read.php
     *
     * @param string $dn
     * @param $filter
     * @param array $fields
     * @param bool  $onlyAttributes
     * @param int   $size
     * @param int   $time
     *
     * @return mixed
     */
    public function read($dn, $filter, array $fields, $onlyAttributes = false, $size = 0, $time = 0);

    /**
     * Performs a single level search on the current connection.
     *
     * @link http://php.net/manual/en/function.ldap-list.php
     *
     * @param string $dn
     * @param string $filter
     * @param array  $attributes
     * @param bool   $onlyAttributes
     * @param int    $size
     * @param int    $time
     *
     * @return mixed
     */
    public function listing($dn, $filter, array $attributes, $onlyAttributes = false, $size = 0, $time = 0);

    /**
     * Adds an entry to the current connection.
     *
     * @link http://php.net/manual/en/function.ldap-add.php
     *
     * @param string $dn
     * @param array  $entry
     *
     * @return bool
     */
    public function add($dn, array $entry);

    /**
     * Deletes an entry on the current connection.
     *
     * @link http://php.net/manual/en/function.ldap-delete.php
     *
     * @param string $dn
     *
     * @return bool
     */
    public function delete($dn);

    /**
     * Modify the name of an entry on the current connection.
     *
     * @link http://php.net/manual/en/function.ldap-rename.php
     *
     * @param string $dn
     * @param string $newRdn
     * @param string $newParent
     * @param bool   $deleteOldRdn
     *
     * @return bool
     */
    public function rename($dn, $newRdn, $newParent, $deleteOldRdn = false);

    /**
     * Modifies an existing entry on the current connection.
     *
     * @link http://php.net/manual/en/function.ldap-modify.php
     *
     * @param string $dn
     * @param array  $entry
     *
     * @return bool
     */
    public function modify($dn, array $entry);

    /**
     * Batch modifies an existing entry on the current connection.
     *
     * @link http://php.net/manual/en/function.ldap-modify-batch.php
     *
     * @param string $dn
     * @param array  $values
     *
     * @return mixed
     */
    public function modifyBatch($dn, array $values);

    /**
     * Add attribute values to current attributes.
     *
     * @link http://php.net/manual/en/function.ldap-mod-add.php
     *
     * @param string $dn
     * @param array  $entry
     *
     * @return mixed
     */
    public function modAdd($dn, array $entry);

    /**
     * Replaces attribute values with new ones.
     *
     * @link http://php.net/manual/en/function.ldap-mod-replace.php
     *
     * @param string $dn
     * @param array  $entry
     *
     * @return mixed
     */
    public function modReplace($dn, array $entry);

    /**
     * Delete attribute values from current attributes.
     *
     * @link http://php.net/manual/en/function.ldap-mod-del.php
     *
     * @param string $dn
     * @param array  $entry
     *
     * @return mixed
     */
    public function modDelete($dn, array $entry);

    /**
     * Send LDAP pagination control.
     *
     * @link http://php.net/manual/en/function.ldap-control-paged-result.php
     *
     * @param int    $pageSize
     * @param bool   $isCritical
     * @param string $cookie
     *
     * @return mixed
     */
    public function controlPagedResult($pageSize = 1000, $isCritical = false, $cookie = '');

    /**
     * Retrieve the LDAP pagination cookie.
     *
     * @link http://php.net/manual/en/function.ldap-control-paged-result-response.php
     *
     * @param $result
     * @param string $cookie
     *
     * @return mixed
     */
    public function controlPagedResultResponse($result, &$cookie);

    /**
     * Frees up the memory allocated internally to store the result.
     *
     * @link https://www.php.net/manual/en/function.ldap-free-result.php
     *
     * @param resource $result
     *
     * @return bool
     */
    public function freeResult($result);

    /**
     * Returns the error number of the last command
     * executed on the current connection.
     *
     * @link http://php.net/manual/en/function.ldap-errno.php
     *
     * @return int
     */
    public function errNo();

    /**
     * Returns the extended error string of the last command.
     *
     * @return string
     */
    public function getExtendedError();

    /**
     * Returns the extended error hex code of the last command.
     *
     * @return string|null
     */
    public function getExtendedErrorHex();

    /**
     * Returns the extended error code of the last command.
     *
     * @return string
     */
    public function getExtendedErrorCode();

    /**
     * Returns the error string of the specified
     * error number.
     *
     * @link http://php.net/manual/en/function.ldap-err2str.php
     *
     * @param int $number
     *
     * @return string
     */
    public function err2Str($number);

    /**
     * Return the diagnostic Message.
     *
     * @return string
     */
    public function getDiagnosticMessage();

    /**
     * Extract the diagnostic code from the message.
     *
     * @param string $message
     *
     * @return string|bool
     */
    public function extractDiagnosticCode($message);
}
