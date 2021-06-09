<?php

namespace LdapRecord\Testing;

use Exception;
use LdapRecord\DetailedError;
use LdapRecord\LdapBase;
use PHPUnit\Framework\Assert as PHPUnit;
use PHPUnit\Framework\Constraint\Constraint;

class LdapFake extends LdapBase
{
    /**
     * The expectations of the LDAP fake.
     *
     * @var array
     */
    protected $expectations = [];

    /**
     * The default fake error number.
     *
     * @var int
     */
    protected $errNo = 1;

    /**
     * The default fake last error string.
     *
     * @var string
     */
    protected $lastError = '';

    /**
     * The default fake diagnostic message string.
     *
     * @var string
     */
    protected $diagnosticMessage = '';

    /**
     * Create a new expected operation.
     *
     * @param string $method
     *
     * @return LdapExpectation
     */
    public static function operation($method)
    {
        return new LdapExpectation($method);
    }

    /**
     * Set the user that will pass binding.
     *
     * @param string $dn
     *
     * @return $this
     */
    public function shouldAuthenticateWith($dn)
    {
        return $this->expect(
            static::operation('bind')->with($dn, PHPUnit::anything())->andReturn(true)
        );
    }

    /**
     * Add an LDAP method expectation.
     *
     * @param LdapExpectation|array $expectations
     *
     * @return $this
     */
    public function expect($expectations = [])
    {
        $expectations = is_array($expectations) ? $expectations : [$expectations];

        foreach ($expectations as $key => $expectation) {
            // If the key is non-numeric, we will assume
            // that the string is the method name and
            // the expectation is the return value.
            if (! is_numeric($key)) {
                $expectation = static::operation($key)->andReturn($expectation);
            }

            if (! $expectation instanceof LdapExpectation) {
                $expectation = static::operation($expectation);
            }

            $this->expectations[$expectation->getMethod()][] = $expectation;
        }

        return $this;
    }

    /**
     * Determine if the method has any expectations.
     *
     * @param string $method
     *
     * @return bool
     */
    protected function hasExpectations($method)
    {
        return count($this->getExpectations($method)) > 0;
    }

    /**
     * Get expectations by method.
     *
     * @param string $method
     *
     * @return LdapExpectation[]|mixed
     */
    protected function getExpectations($method)
    {
        return $this->expectations[$method] ?? [];
    }

    /**
     * Remove an expectation by method and key.
     *
     * @param string $method
     * @param int    $key
     *
     * @return void
     */
    protected function removeExpectation($method, $key)
    {
        unset($this->expectations[$method][$key]);
    }

    /**
     * Set the error number of a failed bind attempt.
     *
     * @param int $number
     *
     * @return $this
     */
    public function shouldReturnErrorNumber($number = 1)
    {
        $this->errNo = $number;

        return $this;
    }

    /**
     * Set the last error of a failed bind attempt.
     *
     * @param string $message
     *
     * @return $this
     */
    public function shouldReturnError($message = '')
    {
        $this->lastError = $message;

        return $this;
    }

    /**
     * Set the diagnostic message of a failed bind attempt.
     *
     * @param string $message
     *
     * @return $this
     */
    public function shouldReturnDiagnosticMessage($message = '')
    {
        $this->diagnosticMessage = $message;

        return $this;
    }

    /**
     * Return a fake error number.
     *
     * @return int
     */
    public function errNo()
    {
        return $this->errNo;
    }

    /**
     * Return a fake error.
     *
     * @return string
     */
    public function getLastError()
    {
        return $this->lastError;
    }

    /**
     * @inheritdoc
     */
    public function getDiagnosticMessage()
    {
        return $this->diagnosticMessage;
    }

    /**
     * Return a fake detailed error.
     *
     * @return DetailedError
     */
    public function getDetailedError()
    {
        return new DetailedError(
            $this->errNo(),
            $this->getLastError(),
            $this->getDiagnosticMessage()
        );
    }

    /**
     * @inheritdoc
     */
    public function getEntries($searchResults)
    {
        return $searchResults;
    }

    /**
     * @inheritdoc
     */
    public function isUsingSSL()
    {
        return $this->hasExpectations('isUsingSSL')
            ? $this->resolveExpectation('isUsingSSL')
            : parent::isUsingSSL();
    }

    /**
     * @inheritdoc
     */
    public function isUsingTLS()
    {
        return $this->hasExpectations('isUsingTLS')
            ? $this->resolveExpectation('isUsingTLS')
            : parent::isUsingTLS();
    }

    /**
     * @inheritdoc
     */
    public function isBound()
    {
        return $this->hasExpectations('isBound')
            ? $this->resolveExpectation('isBound')
            : parent::isBound();
    }

    /**
     * @inheritdoc
     */
    public function setOption($option, $value)
    {
        return $this->hasExpectations('setOption')
            ? $this->resolveExpectation('setOption', func_get_args())
            : true;
    }

    /**
     * @inheritdoc
     */
    public function getOption($option, &$value = null)
    {
        return $this->resolveExpectation('getOption', func_get_args());
    }

    /**
     * @inheritdoc
     */
    public function startTLS()
    {
        return $this->resolveExpectation('startTLS', func_get_args());
    }

    /**
     * @inheritdoc
     */
    public function connect($hosts = [], $port = 389)
    {
        $this->bound = false;

        $this->host = $this->getConnectionString($hosts, $port);

        return $this->connection = $this->hasExpectations('connect')
            ? $this->resolveExpectation('connect', func_get_args())
            : true;
    }

    /**
     * @inheritdoc
     */
    public function close()
    {
        $this->connection = null;
        $this->bound = false;
        $this->host = null;

        return $this->hasExpectations('close')
            ? $this->resolveExpectation('close')
            : true;
    }

    /**
     * @inheritdoc
     */
    public function bind($username, $password)
    {
        return $this->bound = $this->resolveExpectation('bind', func_get_args());
    }

    /**
     * @inheritdoc
     */
    public function search($dn, $filter, array $fields, $onlyAttributes = false, $size = 0, $time = 0, $deref = null, $serverControls = [])
    {
        return $this->resolveExpectation('search', func_get_args());
    }

    /**
     * @inheritdoc
     */
    public function listing($dn, $filter, array $fields, $onlyAttributes = false, $size = 0, $time = 0, $deref = null, $serverControls = [])
    {
        return $this->resolveExpectation('listing', func_get_args());
    }

    /**
     * @inheritdoc
     */
    public function read($dn, $filter, array $fields, $onlyAttributes = false, $size = 0, $time = 0, $deref = null, $serverControls = [])
    {
        return $this->resolveExpectation('read', func_get_args());
    }

    /**
     * @inheritdoc
     */
    public function parseResult($result, &$errorCode, &$dn, &$errorMessage, &$referrals, &$serverControls = [])
    {
        return $this->resolveExpectation('parseResult', func_get_args());
    }

    /**
     * @inheritdoc
     */
    public function add($dn, array $entry)
    {
        return $this->resolveExpectation('add', func_get_args());
    }

    /**
     * @inheritdoc
     */
    public function delete($dn)
    {
        return $this->resolveExpectation('delete', func_get_args());
    }

    /**
     * @inheritdoc
     */
    public function rename($dn, $newRdn, $newParent, $deleteOldRdn = false)
    {
        return $this->resolveExpectation('rename', func_get_args());
    }

    /**
     * @inheritdoc
     */
    public function modify($dn, array $entry)
    {
        return $this->resolveExpectation('modify', func_get_args());
    }

    /**
     * @inheritdoc
     */
    public function modifyBatch($dn, array $values)
    {
        return $this->resolveExpectation('modifyBatch', func_get_args());
    }

    /**
     * @inheritdoc
     */
    public function modAdd($dn, array $entry)
    {
        return $this->resolveExpectation('modAdd', func_get_args());
    }

    /**
     * @inheritdoc
     */
    public function modReplace($dn, array $entry)
    {
        return $this->resolveExpectation('modReplace', func_get_args());
    }

    /**
     * @inheritdoc
     */
    public function modDelete($dn, array $entry)
    {
        return $this->resolveExpectation('modDelete', func_get_args());
    }

    /**
     * @inheritdoc
     */
    public function controlPagedResult($pageSize = 1000, $isCritical = false, $cookie = '')
    {
        return $this->resolveExpectation('controlPagedResult', func_get_args());
    }

    /**
     * @inheritdoc
     */
    public function controlPagedResultResponse($result, &$cookie)
    {
        return $this->resolveExpectation('controlPagedResultResponse', func_get_args());
    }

    /**
     * @inheritdoc
     */
    public function freeResult($result)
    {
        return $this->resolveExpectation('freeResult', func_get_args());
    }

    /**
     * @inheritdoc
     */
    public function err2Str($number)
    {
        return $this->resolveExpectation('err2Str', func_get_args());
    }

    /**
     * Resolve the methods expectations.
     *
     * @param string $method
     * @param array  $args
     *
     * @return mixed
     *
     * @throws Exception
     */
    protected function resolveExpectation($method, $args = [])
    {
        foreach ($this->getExpectations($method) as $key => $expectation) {
            $this->assertMethodArgumentsMatch($method, $expectation->getExpectedArgs(), $args);

            $expectation->decrementCallCount();

            if ($expectation->getExpectedCount() === 0) {
                $this->removeExpectation($method, $key);
            }

            if (! is_null($exception = $expectation->getExpectedException())) {
                throw $exception;
            }

            if ($expectation->isReturningError()) {
                $this->applyExpectationError($expectation);
            }

            return $expectation->getExpectedValue();
        }

        throw new Exception("LDAP method [$method] was unexpected.");
    }

    /**
     * Apply the expectation error to the fake.
     *
     * @param LdapExpectation $expectation
     *
     * @return void
     */
    protected function applyExpectationError(LdapExpectation $expectation)
    {
        $this->shouldReturnError($expectation->getExpectedErrorMessage());
        $this->shouldReturnErrorNumber($expectation->getExpectedErrorCode());
        $this->shouldReturnDiagnosticMessage($expectation->getExpectedErrorDiagnosticMessage());
    }

    /**
     * Assert that the expected arguments match the operations arguments.
     *
     * @param string       $method
     * @param Constraint[] $expectedArgs
     * @param array        $methodArgs
     *
     * @return void
     */
    protected function assertMethodArgumentsMatch($method, $expectedArgs = [], $methodArgs = [])
    {
        foreach ($expectedArgs as $key => $constraint) {
            $argNumber = $key + 1;

            PHPUnit::assertArrayHasKey(
                $key,
                $methodArgs,
                "LDAP method [$method] argument #{$argNumber} does not exist."
            );

            $constraint->evaluate(
                $methodArgs[$key],
                "LDAP method [$method] expectation failed."
            );
        }
    }
}
