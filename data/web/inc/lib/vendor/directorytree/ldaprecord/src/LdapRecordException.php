<?php

namespace LdapRecord;

use Exception;

class LdapRecordException extends Exception
{
    /**
     * The detailed LDAP error (if available).
     *
     * @var DetailedError|null
     */
    protected $detailedError;

    /**
     * Create a new Bind Exception with a detailed connection error.
     *
     * @param Exception          $e
     * @param DetailedError|null $error
     *
     * @return $this
     */
    public static function withDetailedError(Exception $e, DetailedError $error = null)
    {
        return (new static($e->getMessage(), $e->getCode(), $e))->setDetailedError($error);
    }

    /**
     * Set the detailed error.
     *
     * @param DetailedError|null $error
     *
     * @return $this
     */
    public function setDetailedError(DetailedError $error = null)
    {
        $this->detailedError = $error;

        return $this;
    }

    /**
     * Returns the detailed error.
     *
     * @return DetailedError|null
     */
    public function getDetailedError()
    {
        return $this->detailedError;
    }
}
