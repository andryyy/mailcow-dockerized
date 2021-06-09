<?php

namespace LdapRecord;

trait DetectsErrors
{
    /**
     * Determine if the error was caused by a lost connection.
     *
     * @param string $error
     *
     * @return bool
     */
    protected function causedByLostConnection($error)
    {
        return $this->errorContainsMessage($error, ["Can't contact LDAP server", 'Operations error']);
    }

    /**
     * Determine if the error was caused by lack of pagination support.
     *
     * @param string $error
     *
     * @return bool
     */
    protected function causedByPaginationSupport($error)
    {
        return $this->errorContainsMessage($error, 'No server controls in result');
    }

    /**
     * Determine if the error was caused by a size limit warning.
     *
     * @param $error
     *
     * @return bool
     */
    protected function causedBySizeLimit($error)
    {
        return $this->errorContainsMessage($error, ['Partial search results returned', 'Size limit exceeded']);
    }

    /**
     * Determine if the error was caused by a "No such object" warning.
     *
     * @param string $error
     *
     * @return bool
     */
    protected function causedByNoSuchObject($error)
    {
        return $this->errorContainsMessage($error, ['No such object']);
    }

    /**
     * Determine if the error contains the any of the messages.
     *
     * @param string       $error
     * @param string|array $messages
     *
     * @return bool
     */
    protected function errorContainsMessage($error, $messages = [])
    {
        foreach ((array) $messages as $message) {
            if (strpos($error, $message) !== false) {
                return true;
            }
        }

        return false;
    }
}
