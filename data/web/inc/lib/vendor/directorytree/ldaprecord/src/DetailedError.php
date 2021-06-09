<?php

namespace LdapRecord;

class DetailedError
{
    /**
     * The error code from ldap_errno.
     *
     * @var int|null
     */
    protected $errorCode;

    /**
     * The error message from ldap_error.
     *
     * @var string|null
     */
    protected $errorMessage;

    /**
     * The diagnostic message when retrieved after an ldap_error.
     *
     * @var string|null
     */
    protected $diagnosticMessage;

    /**
     * Constructor.
     *
     * @param int    $errorCode
     * @param string $errorMessage
     * @param string $diagnosticMessage
     */
    public function __construct($errorCode, $errorMessage, $diagnosticMessage)
    {
        $this->errorCode = $errorCode;
        $this->errorMessage = $errorMessage;
        $this->diagnosticMessage = $diagnosticMessage;
    }

    /**
     * Returns the LDAP error code.
     *
     * @return int
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * Returns the LDAP error message.
     *
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * Returns the LDAP diagnostic message.
     *
     * @return string
     */
    public function getDiagnosticMessage()
    {
        return $this->diagnosticMessage;
    }
}
