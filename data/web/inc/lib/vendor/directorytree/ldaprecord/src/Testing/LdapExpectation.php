<?php

namespace LdapRecord\Testing;

use LdapRecord\LdapRecordException;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Constraint\IsEqual;
use UnexpectedValueException;

class LdapExpectation
{
    /**
     * The value to return from the expectation.
     *
     * @var mixed
     */
    protected $value;

    /**
     * The exception to throw from the expectation.
     *
     * @var null|LdapRecordException|\Exception
     */
    protected $exception;

    /**
     * The amount of times the expectation should be called.
     *
     * @var int
     */
    protected $count = 1;

    /**
     * The method that the expectation belongs to.
     *
     * @var string
     */
    protected $method;

    /**
     * The methods argument's.
     *
     * @var array
     */
    protected $args = [];

    /**
     * Whether the same expectation should be returned indefinitely.
     *
     * @var bool
     */
    protected $indefinitely = true;

    /**
     * Whether the expectation should return errors.
     *
     * @var bool
     */
    protected $errors = false;

    /**
     * The error number to return.
     *
     * @var int
     */
    protected $errorCode = 1;

    /**
     * The last error string to return.
     *
     * @var string
     */
    protected $errorMessage = '';

    /**
     * The diagnostic message string to return.
     *
     * @var string
     */
    protected $errorDiagnosticMessage = '';

    /**
     * Constructor.
     *
     * @param string $method
     */
    public function __construct($method)
    {
        $this->method = $method;
    }

    /**
     * Set the arguments that the operation should receive.
     *
     * @param mixed $args
     *
     * @return $this
     */
    public function with($args)
    {
        $args = is_array($args) ? $args : func_get_args();

        foreach ($args as $key => $arg) {
            if (! $arg instanceof Constraint) {
                $args[$key] = new IsEqual($arg);
            }
        }

        $this->args = $args;

        return $this;
    }

    /**
     * Set the expected value to return.
     *
     * @param mixed $value
     *
     * @return $this
     */
    public function andReturn($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * The error message to return from the expectation.
     *
     * @param int    $code
     * @param string $error
     * @param string $diagnosticMessage
     *
     * @return $this
     */
    public function andReturnError($code = 1, $error = '', $diagnosticMessage = '')
    {
        $this->errors = true;

        $this->errorCode = $code;
        $this->errorMessage = $error;
        $this->errorDiagnosticMessage = $diagnosticMessage;

        return $this;
    }

    /**
     * Set the expected exception to throw.
     *
     * @param string|\Exception|LdapRecordException $exception
     *
     * @return $this
     */
    public function andThrow($exception)
    {
        if (is_string($exception)) {
            $exception = new LdapRecordException($exception);
        }

        $this->exception = $exception;

        return $this;
    }

    /**
     * Set the expectation to be only called once.
     *
     * @return $this
     */
    public function once()
    {
        return $this->times(1);
    }

    /**
     * Set the expectation to be only called twice.
     *
     * @return $this
     */
    public function twice()
    {
        return $this->times(2);
    }

    /**
     * Set the expectation to be called the given number of times.
     *
     * @param int $count
     *
     * @return $this
     */
    public function times($count = 1)
    {
        $this->indefinitely = false;

        $this->count = $count;

        return $this;
    }

    /**
     * Get the method the expectation belongs to.
     *
     * @return string
     */
    public function getMethod()
    {
        if (is_null($this->method)) {
            throw new UnexpectedValueException('The [$method] property cannot be null.');
        }

        return $this->method;
    }

    /**
     * Get the expected call count.
     *
     * @return int
     */
    public function getExpectedCount()
    {
        return $this->count;
    }

    /**
     * Get the expected arguments.
     *
     * @return array
     */
    public function getExpectedArgs()
    {
        return $this->args;
    }

    /**
     * Get the expected exception.
     *
     * @return null|\Exception|LdapRecordException
     */
    public function getExpectedException()
    {
        return $this->exception;
    }

    /**
     * Get the expected value.
     *
     * @return mixed
     */
    public function getExpectedValue()
    {
        return $this->value;
    }

    /**
     * Determine whether the expectation is returning an error.
     *
     * @return bool
     */
    public function isReturningError()
    {
        return $this->errors;
    }

    /**
     * @return int
     */
    public function getExpectedErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * @return string
     */
    public function getExpectedErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * @return string
     */
    public function getExpectedErrorDiagnosticMessage()
    {
        return $this->errorDiagnosticMessage;
    }

    /**
     * Decrement the call count of the expectation.
     *
     * @return $this
     */
    public function decrementCallCount()
    {
        if (! $this->indefinitely) {
            $this->count -= 1;
        }

        return $this;
    }
}
