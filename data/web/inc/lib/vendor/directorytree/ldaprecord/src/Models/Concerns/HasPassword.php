<?php

namespace LdapRecord\Models\Concerns;

use LdapRecord\ConnectionException;
use LdapRecord\LdapRecordException;
use LdapRecord\Models\Attributes\Password;

trait HasPassword
{
    /**
     * Set the password on the user.
     *
     * @param string|array $password
     *
     * @throws ConnectionException
     */
    public function setPasswordAttribute($password)
    {
        $this->validateSecureConnection();

        // Here we will attempt to determine the password hash method in use
        // by parsing the users hashed password (if it as available). If a
        // method is determined, we will override the default here.
        if (! ($method = $this->determinePasswordHashMethod())) {
            $method = $this->getPasswordHashMethod();
        }

        // If the password given is an array, we can assume we
        // are changing the password for the current user.
        if (is_array($password)) {
            $this->setChangedPassword(
                $this->getHashedPassword($method, $password[0], $this->getPasswordSalt($method)),
                $this->getHashedPassword($method, $password[1]),
                $this->getPasswordAttributeName()
            );
        }
        // Otherwise, we will assume the password is being
        // reset, overwriting the one currently in place.
        else {
            $this->setPassword(
                $this->getHashedPassword($method, $password),
                $this->getPasswordAttributeName()
            );
        }
    }

    /**
     * Alias for setting the password on the user.
     *
     * @param string|array $password
     *
     * @throws ConnectionException
     */
    public function setUnicodepwdAttribute($password)
    {
        $this->setPasswordAttribute($password);
    }

    /**
     * An accessor for retrieving the user's hashed password value.
     *
     * @return string|null
     */
    public function getPasswordAttribute()
    {
        return $this->getAttribute($this->getPasswordAttributeName())[0] ?? null;
    }

    /**
     * Get the name of the attribute that contains the user's password.
     *
     * @return string
     */
    public function getPasswordAttributeName()
    {
        if (property_exists($this, 'passwordAttribute')) {
            return $this->passwordAttribute;
        }

        if (method_exists($this, 'passwordAttribute')) {
            return $this->passwordAttribute();
        }

        return 'unicodepwd';
    }

    /**
     * Get the name of the method to use for hashing the user's password.
     *
     * @return string
     */
    public function getPasswordHashMethod()
    {
        if (property_exists($this, 'passwordHashMethod')) {
            return $this->passwordHashMethod;
        }

        if (method_exists($this, 'passwordHashMethod')) {
            return $this->passwordHashMethod();
        }

        return 'encode';
    }

    /**
     * Set the changed password.
     *
     * @param string $oldPassword
     * @param string $newPassword
     * @param string $attribute
     *
     * @return void
     */
    protected function setChangedPassword($oldPassword, $newPassword, $attribute)
    {
        // Create batch modification for removing the old password.
        $this->addModification(
            $this->newBatchModification(
                $attribute,
                LDAP_MODIFY_BATCH_REMOVE,
                [$oldPassword]
            )
        );

        // Create batch modification for adding the new password.
        $this->addModification(
            $this->newBatchModification(
                $attribute,
                LDAP_MODIFY_BATCH_ADD,
                [$newPassword]
            )
        );
    }

    /**
     * Set the password on the model.
     *
     * @param string $password
     * @param string $attribute
     *
     * @return void
     */
    protected function setPassword($password, $attribute)
    {
        $this->addModification(
            $this->newBatchModification(
                $attribute,
                LDAP_MODIFY_BATCH_REPLACE,
                [$password]
            )
        );
    }

    /**
     * Encode / hash the given password.
     *
     * @param string $method
     * @param string $password
     * @param string $salt
     *
     * @return string
     *
     * @throws LdapRecordException
     */
    protected function getHashedPassword($method, $password, $salt = null)
    {
        if (! method_exists(Password::class, $method)) {
            throw new LdapRecordException("Password hashing method [{$method}] does not exist.");
        }

        if (Password::hashMethodRequiresSalt($method)) {
            return Password::{$method}($password, $salt);
        }

        return Password::{$method}($password);
    }

    /**
     * Validates that the current LDAP connection is secure.
     *
     * @return void
     *
     * @throws ConnectionException
     */
    protected function validateSecureConnection()
    {
        if (! $this->getConnection()->getLdapConnection()->canChangePasswords()) {
            throw new ConnectionException(
                'You must be connected to your LDAP server with TLS or SSL to perform this operation.'
            );
        }
    }

    /**
     * Attempt to retrieve the password's salt.
     *
     * @param string $method
     *
     * @return string|null
     */
    public function getPasswordSalt($method)
    {
        if (! Password::hashMethodRequiresSalt($method)) {
            return;
        }

        return Password::getSalt($this->password);
    }

    /**
     * Determine the password hash method to use from the users current password.
     *
     * @return string|void
     */
    public function determinePasswordHashMethod()
    {
        if (! $password = $this->password) {
            return;
        }

        if (! $method = Password::getHashMethod($password)) {
            return;
        }

        [,$algo] = array_pad(
            Password::getHashMethodAndAlgo($password) ?? [],
            $length = 2,
            $value = null
        );

        switch ($algo) {
            case Password::CRYPT_SALT_TYPE_MD5:
                return 'md5'.$method;
            case Password::CRYPT_SALT_TYPE_SHA256:
                return 'sha256'.$method;
            case Password::CRYPT_SALT_TYPE_SHA512:
                return 'sha512'.$method;
            default:
                return $method;
        }
    }
}
