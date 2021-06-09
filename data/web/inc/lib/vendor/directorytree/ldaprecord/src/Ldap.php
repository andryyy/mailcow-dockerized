<?php

namespace LdapRecord;

class Ldap extends LdapBase
{
    /**
     * @inheritdoc
     */
    public function getEntries($searchResults)
    {
        return $this->executeFailableOperation(function () use ($searchResults) {
            return ldap_get_entries($this->connection, $searchResults);
        });
    }

    /**
     * Retrieves the first entry from a search result.
     *
     * @link http://php.net/manual/en/function.ldap-first-entry.php
     *
     * @param resource $searchResult
     *
     * @return resource
     */
    public function getFirstEntry($searchResults)
    {
        return $this->executeFailableOperation(function () use ($searchResults) {
            return ldap_first_entry($this->connection, $searchResults);
        });
    }

    /**
     * Retrieves the next entry from a search result.
     *
     * @link http://php.net/manual/en/function.ldap-next-entry.php
     *
     * @param resource $entry
     *
     * @return resource
     */
    public function getNextEntry($entry)
    {
        return $this->executeFailableOperation(function () use ($entry) {
            return ldap_next_entry($this->connection, $entry);
        });
    }

    /**
     * Retrieves the ldap entry's attributes.
     *
     * @link http://php.net/manual/en/function.ldap-get-attributes.php
     *
     * @param resource $entry
     *
     * @return array|false
     */
    public function getAttributes($entry)
    {
        return $this->executeFailableOperation(function () use ($entry) {
            return ldap_get_attributes($this->connection, $entry);
        });
    }

    /**
     * Returns the number of entries from a search result.
     *
     * @link http://php.net/manual/en/function.ldap-count-entries.php
     *
     * @param resource $searchResult
     *
     * @return int
     */
    public function countEntries($searchResults)
    {
        return $this->executeFailableOperation(function () use ($searchResults) {
            return ldap_count_entries($this->connection, $searchResults);
        });
    }

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
    public function compare($dn, $attribute, $value)
    {
        return $this->executeFailableOperation(function () use ($dn, $attribute, $value) {
            return ldap_compare($this->connection, $dn, $attribute, $value);
        });
    }

    /**
     * @inheritdoc
     */
    public function getLastError()
    {
        if (! $this->connection) {
            return;
        }

        return ldap_error($this->connection);
    }

    /**
     * @inheritdoc
     */
    public function getDetailedError()
    {
        if (! $number = $this->errNo()) {
            return;
        }

        $this->getOption(LDAP_OPT_DIAGNOSTIC_MESSAGE, $message);

        return new DetailedError($number, $this->err2Str($number), $message);
    }

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
    public function getValuesLen($entry, $attribute)
    {
        return $this->executeFailableOperation(function () use ($entry, $attribute) {
            return ldap_get_values_len($this->connection, $entry, $attribute);
        });
    }

    /**
     * @inheritdoc
     */
    public function setOption($option, $value)
    {
        return ldap_set_option($this->connection, $option, $value);
    }

    /**
     * @inheritdoc
     */
    public function getOption($option, &$value = null)
    {
        ldap_get_option($this->connection, $option, $value);

        return $value;
    }

    /**
     * Set a callback function to do re-binds on referral chasing.
     *
     * @link http://php.net/manual/en/function.ldap-set-rebind-proc.php
     *
     * @param callable $callback
     *
     * @return bool
     */
    public function setRebindCallback(callable $callback)
    {
        return ldap_set_rebind_proc($this->connection, $callback);
    }

    /**
     * @inheritdoc
     */
    public function startTLS()
    {
        return $this->executeFailableOperation(function () {
            return ldap_start_tls($this->connection);
        });
    }

    /**
     * @inheritdoc
     */
    public function connect($hosts = [], $port = 389)
    {
        $this->bound = false;

        $this->host = $this->getConnectionString($hosts, $port);

        return $this->connection = $this->executeFailableOperation(function () {
            return ldap_connect($this->host);
        });
    }

    /**
     * @inheritdoc
     */
    public function close()
    {
        $result = is_resource($this->connection) ? @ldap_close($this->connection) : false;

        $this->connection = null;
        $this->bound = false;
        $this->host = null;

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function search($dn, $filter, array $fields, $onlyAttributes = false, $size = 0, $time = 0, $deref = null, $serverControls = [])
    {
        return $this->executeFailableOperation(function () use (
            $dn,
            $filter,
            $fields,
            $onlyAttributes,
            $size,
            $time,
            $deref,
            $serverControls
        ) {
            return $this->supportsServerControlsInMethods() && ! empty($serverControls)
                ? ldap_search($this->connection, $dn, $filter, $fields, $onlyAttributes, $size, $time, $deref, $serverControls)
                : ldap_search($this->connection, $dn, $filter, $fields, $onlyAttributes, $size, $time, $deref);
        });
    }

    /**
     * @inheritdoc
     */
    public function listing($dn, $filter, array $fields, $onlyAttributes = false, $size = 0, $time = 0, $deref = null, $serverControls = [])
    {
        return $this->executeFailableOperation(function () use (
            $dn,
            $filter,
            $fields,
            $onlyAttributes,
            $size,
            $time,
            $deref,
            $serverControls
        ) {
            return $this->supportsServerControlsInMethods() && ! empty($serverControls)
                ? ldap_list($this->connection, $dn, $filter, $fields, $onlyAttributes, $size, $time, $deref, $serverControls)
                : ldap_list($this->connection, $dn, $filter, $fields, $onlyAttributes, $size, $time, $deref);
        });
    }

    /**
     * @inheritdoc
     */
    public function read($dn, $filter, array $fields, $onlyAttributes = false, $size = 0, $time = 0, $deref = null, $serverControls = [])
    {
        return $this->executeFailableOperation(function () use (
            $dn,
            $filter,
            $fields,
            $onlyAttributes,
            $size,
            $time,
            $deref,
            $serverControls
        ) {
            return $this->supportsServerControlsInMethods() && ! empty($serverControls)
                ? ldap_read($this->connection, $dn, $filter, $fields, $onlyAttributes, $size, $time, $deref, $serverControls)
                : ldap_read($this->connection, $dn, $filter, $fields, $onlyAttributes, $size, $time, $deref);
        });
    }

    /**
     * @inheritdoc
     */
    public function parseResult($result, &$errorCode, &$dn, &$errorMessage, &$referrals, &$serverControls = [])
    {
        return $this->executeFailableOperation(function () use (
            $result,
            &$errorCode,
            &$dn,
            &$errorMessage,
            &$referrals,
            &$serverControls
        ) {
            return $this->supportsServerControlsInMethods() && ! empty($serverControls)
                ? ldap_parse_result($this->connection, $result, $errorCode, $dn, $errorMessage, $referrals, $serverControls)
                : ldap_parse_result($this->connection, $result, $errorCode, $dn, $errorMessage, $referrals);
        });
    }

    /**
     * @inheritdoc
     */
    public function bind($username, $password)
    {
        return $this->bound = $this->executeFailableOperation(function () use ($username, $password) {
            return ldap_bind($this->connection, $username, html_entity_decode($password));
        });
    }

    /**
     * @inheritdoc
     */
    public function add($dn, array $entry)
    {
        return $this->executeFailableOperation(function () use ($dn, $entry) {
            return ldap_add($this->connection, $dn, $entry);
        });
    }

    /**
     * @inheritdoc
     */
    public function delete($dn)
    {
        return $this->executeFailableOperation(function () use ($dn) {
            return ldap_delete($this->connection, $dn);
        });
    }

    /**
     * @inheritdoc
     */
    public function rename($dn, $newRdn, $newParent, $deleteOldRdn = false)
    {
        return $this->executeFailableOperation(function () use (
            $dn,
            $newRdn,
            $newParent,
            $deleteOldRdn
        ) {
            return ldap_rename($this->connection, $dn, $newRdn, $newParent, $deleteOldRdn);
        });
    }

    /**
     * @inheritdoc
     */
    public function modify($dn, array $entry)
    {
        return $this->executeFailableOperation(function () use ($dn, $entry) {
            return ldap_modify($this->connection, $dn, $entry);
        });
    }

    /**
     * @inheritdoc
     */
    public function modifyBatch($dn, array $values)
    {
        return $this->executeFailableOperation(function () use ($dn, $values) {
            return ldap_modify_batch($this->connection, $dn, $values);
        });
    }

    /**
     * @inheritdoc
     */
    public function modAdd($dn, array $entry)
    {
        return $this->executeFailableOperation(function () use ($dn, $entry) {
            return ldap_mod_add($this->connection, $dn, $entry);
        });
    }

    /**
     * @inheritdoc
     */
    public function modReplace($dn, array $entry)
    {
        return $this->executeFailableOperation(function () use ($dn, $entry) {
            return ldap_mod_replace($this->connection, $dn, $entry);
        });
    }

    /**
     * @inheritdoc
     */
    public function modDelete($dn, array $entry)
    {
        return $this->executeFailableOperation(function () use ($dn, $entry) {
            return ldap_mod_del($this->connection, $dn, $entry);
        });
    }

    /**
     * @inheritdoc
     */
    public function controlPagedResult($pageSize = 1000, $isCritical = false, $cookie = '')
    {
        return $this->executeFailableOperation(function () use ($pageSize, $isCritical, $cookie) {
            return ldap_control_paged_result($this->connection, $pageSize, $isCritical, $cookie);
        });
    }

    /**
     * @inheritdoc
     */
    public function controlPagedResultResponse($result, &$cookie, &$estimated = null)
    {
        return $this->executeFailableOperation(function () use ($result, &$cookie, &$estimated) {
            return ldap_control_paged_result_response($this->connection, $result, $cookie, $estimated);
        });
    }

    /**
     * @inheritdoc
     */
    public function freeResult($result)
    {
        return ldap_free_result($result);
    }

    /**
     * @inheritdoc
     */
    public function errNo()
    {
        return $this->connection
            ? ldap_errno($this->connection)
            : null;
    }

    /**
     * @inheritdoc
     */
    public function err2Str($number)
    {
        return ldap_err2str($number);
    }

    /**
     * Returns the extended error hex code of the last command.
     *
     * @return string|null
     */
    public function getExtendedErrorHex()
    {
        if (preg_match("/(?<=data\s).*?(?=,)/", $this->getExtendedError(), $code)) {
            return $code[0];
        }
    }

    /**
     * Returns the extended error code of the last command.
     *
     * @return bool|string
     */
    public function getExtendedErrorCode()
    {
        return $this->extractDiagnosticCode($this->getExtendedError());
    }

    /**
     * Extract the diagnostic code from the message.
     *
     * @param string $message
     *
     * @return string|bool
     */
    public function extractDiagnosticCode($message)
    {
        preg_match('/^([\da-fA-F]+):/', $message, $matches);

        return isset($matches[1]) ? $matches[1] : false;
    }

    /**
     * @inheritdoc
     */
    public function getDiagnosticMessage()
    {
        $this->getOption(LDAP_OPT_ERROR_STRING, $message);

        return $message;
    }
}
