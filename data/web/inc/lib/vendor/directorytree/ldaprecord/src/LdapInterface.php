<?php

namespace LdapRecord;

interface LdapInterface
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
     * Various useful server control OID's.
     *
     * @see https://ldap.com/ldap-oid-reference-guide/
     * @see http://msdn.microsoft.com/en-us/library/cc223359.aspx
     */
    const OID_SERVER_START_TLS = '1.3.6.1.4.1.1466.20037';
    const OID_SERVER_PAGED_RESULTS = '1.2.840.113556.1.4.319';
    const OID_SERVER_SHOW_DELETED = '1.2.840.113556.1.4.417';
    const OID_SERVER_SORT = '1.2.840.113556.1.4.473';
    const OID_SERVER_CROSSDOM_MOVE_TARGET = '1.2.840.113556.1.4.521';
    const OID_SERVER_NOTIFICATION = '1.2.840.113556.1.4.528';
    const OID_SERVER_EXTENDED_DN = '1.2.840.113556.1.4.529';
    const OID_SERVER_LAZY_COMMIT = '1.2.840.113556.1.4.619';
    const OID_SERVER_SD_FLAGS = '1.2.840.113556.1.4.801';
    const OID_SERVER_TREE_DELETE = '1.2.840.113556.1.4.805';
    const OID_SERVER_DIRSYNC = '1.2.840.113556.1.4.841';
    const OID_SERVER_VERIFY_NAME = '1.2.840.113556.1.4.1338';
    const OID_SERVER_DOMAIN_SCOPE = '1.2.840.113556.1.4.1339';
    const OID_SERVER_SEARCH_OPTIONS = '1.2.840.113556.1.4.1340';
    const OID_SERVER_PERMISSIVE_MODIFY = '1.2.840.113556.1.4.1413';
    const OID_SERVER_ASQ = '1.2.840.113556.1.4.1504';
    const OID_SERVER_FAST_BIND = '1.2.840.113556.1.4.1781';
    const OID_SERVER_CONTROL_VLVREQUEST = '2.16.840.1.113730.3.4.9';

    /**
     * Query OID's.
     *
     * @see https://ldapwiki.com/wiki/LDAP_MATCHING_RULE_IN_CHAIN
     */
    const OID_MATCHING_RULE_IN_CHAIN = '1.2.840.113556.1.4.1941';

    /**
     * Set the current connection to use SSL.
     *
     * @param bool $enabled
     *
     * @return $this
     */
    public function ssl();

    /**
     * Determine if the current connection instance is using SSL.
     *
     * @return bool
     */
    public function isUsingSSL();

    /**
     * Set the current connection to use TLS.
     *
     * @param bool $enabled
     *
     * @return $this
     */
    public function tls();

    /**
     * Determine if the current connection instance is using TLS.
     *
     * @return bool
     */
    public function isUsingTLS();

    /**
     * Determine if the connection is bound.
     *
     * @return bool
     */
    public function isBound();

    /**
     * Determine if the connection has been created.
     *
     * @return bool
     */
    public function isConnected();

    /**
     * Determine the connection is able to modify passwords.
     *
     * @return bool
     */
    public function canChangePasswords();

    /**
     * Returns the full LDAP host URL.
     *
     * Ex: ldap://192.168.1.1:386
     *
     * @return string|null
     */
    public function getHost();

    /**
     * Get the underlying connection resource.
     *
     * @return resource|null
     */
    public function getConnection();

    /**
     * Retrieve the entries from a search result.
     *
     * @link http://php.net/manual/en/function.ldap-get-entries.php
     *
     * @param resource $searchResult
     *
     * @return array
     */
    public function getEntries($searchResults);

    /**
     * Retrieve the last error on the current connection.
     *
     * @link http://php.net/manual/en/function.ldap-error.php
     *
     * @return string|null
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
     * Set an option on the current connection.
     *
     * @link http://php.net/manual/en/function.ldap-set-option.php
     *
     * @param int   $option
     * @param mixed $value
     *
     * @return bool
     */
    public function setOption($option, $value);

    /**
     * Set options on the current connection.
     *
     * @param array $options
     *
     * @return void
     */
    public function setOptions(array $options = []);

    /**
     * Get the value for the LDAP option.
     *
     * @link https://www.php.net/manual/en/function.ldap-get-option.php
     *
     * @param int   $option
     * @param mixed $value
     *
     * @return mixed
     */
    public function getOption($option, &$value = null);

    /**
     * Starts a connection using TLS.
     *
     * @link http://php.net/manual/en/function.ldap-start-tls.php
     *
     * @return bool
     *
     * @throws LdapRecordException
     */
    public function startTLS();

    /**
     * Connects to the specified hostname using the specified port.
     *
     * @link http://php.net/manual/en/function.ldap-start-tls.php
     *
     * @param string|array $hosts
     * @param int          $port
     *
     * @return resource|false
     */
    public function connect($hosts = [], $port = 389);

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
     * @param int    $deref
     * @param array  $serverControls
     *
     * @return resource
     */
    public function search($dn, $filter, array $fields, $onlyAttributes = false, $size = 0, $time = 0, $deref = null, $serverControls = []);

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
     * @param int    $deref
     * @param array  $serverControls
     *
     * @return resource
     */
    public function listing($dn, $filter, array $fields, $onlyAttributes = false, $size = 0, $time = 0, $deref = null, $serverControls = []);

    /**
     * Reads an entry on the current connection.
     *
     * @link http://php.net/manual/en/function.ldap-read.php
     *
     * @param string $dn
     * @param string $filter
     * @param array  $fields
     * @param bool   $onlyAttributes
     * @param int    $size
     * @param int    $time
     * @param int    $deref
     * @param array  $serverControls
     *
     * @return resource
     */
    public function read($dn, $filter, array $fields, $onlyAttributes = false, $size = 0, $time = 0, $deref = null, $serverControls = []);

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
    public function parseResult($result, &$errorCode, &$dn, &$errorMessage, &$referrals, &$serverControls = []);

    /**
     * Binds to the current connection using the specified username and password.
     * If sasl is true, the current connection is bound using SASL.
     *
     * @link http://php.net/manual/en/function.ldap-bind.php
     *
     * @param string $username
     * @param string $password
     *
     * @return bool
     *
     * @throws LdapRecordException
     */
    public function bind($username, $password);

    /**
     * Adds an entry to the current connection.
     *
     * @link http://php.net/manual/en/function.ldap-add.php
     *
     * @param string $dn
     * @param array  $entry
     *
     * @return bool
     *
     * @throws LdapRecordException
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
     *
     * @throws LdapRecordException
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
     *
     * @throws LdapRecordException
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
     *
     * @throws LdapRecordException
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
     * @return bool
     *
     * @throws LdapRecordException
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
     * @return bool
     *
     * @throws LdapRecordException
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
     * @return bool
     *
     * @throws LdapRecordException
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
     * @return bool
     *
     * @throws LdapRecordException
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
     * @return bool
     */
    public function controlPagedResult($pageSize = 1000, $isCritical = false, $cookie = '');

    /**
     * Retrieve the LDAP pagination cookie.
     *
     * @link http://php.net/manual/en/function.ldap-control-paged-result-response.php
     *
     * @param resource $result
     * @param string   $cookie
     *
     * @return bool
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
     * @return int|null
     */
    public function errNo();

    /**
     * Returns the error string of the specified error number.
     *
     * @link http://php.net/manual/en/function.ldap-err2str.php
     *
     * @param int $number
     *
     * @return string
     */
    public function err2Str($number);

    /**
     * Returns the LDAP protocol to utilize for the current connection.
     *
     * @return string
     */
    public function getProtocol();

    /**
     * Returns the extended error code of the last command.
     *
     * @return string
     */
    public function getExtendedError();

    /**
     * Return the diagnostic Message.
     *
     * @return string
     */
    public function getDiagnosticMessage();

    /**
     * Determine if the current PHP version supports server controls.
     *
     * @return bool
     */
    public function supportsServerControlsInMethods();
}
