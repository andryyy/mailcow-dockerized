<?php

namespace LdapRecord\Models\Attributes;

use InvalidArgumentException;
use LdapRecord\LdapRecordException;
use ReflectionMethod;

class Password
{
    const CRYPT_SALT_TYPE_MD5 = 1;
    const CRYPT_SALT_TYPE_SHA256 = 5;
    const CRYPT_SALT_TYPE_SHA512 = 6;

    /**
     * Make an encoded password for transmission over LDAP.
     *
     * @param string $password
     *
     * @return string
     */
    public static function encode($password)
    {
        return iconv('UTF-8', 'UTF-16LE', '"'.$password.'"');
    }

    /**
     * Make a salted md5 password.
     *
     * @param string      $password
     * @param null|string $salt
     *
     * @return string
     */
    public static function smd5($password, $salt = null)
    {
        return '{SMD5}'.static::makeHash($password, 'md5', null, $salt ?? random_bytes(4));
    }

    /**
     * Make a salted SHA password.
     *
     * @param string      $password
     * @param null|string $salt
     *
     * @return string
     */
    public static function ssha($password, $salt = null)
    {
        return '{SSHA}'.static::makeHash($password, 'sha1', null, $salt ?? random_bytes(4));
    }

    /**
     * Make a salted SSHA256 password.
     *
     * @param string      $password
     * @param null|string $salt
     *
     * @return string
     */
    public static function ssha256($password, $salt = null)
    {
        return '{SSHA256}'.static::makeHash($password, 'hash', 'sha256', $salt ?? random_bytes(4));
    }

    /**
     * Make a salted SSHA384 password.
     *
     * @param string      $password
     * @param null|string $salt
     *
     * @return string
     */
    public static function ssha384($password, $salt = null)
    {
        return '{SSHA384}'.static::makeHash($password, 'hash', 'sha384', $salt ?? random_bytes(4));
    }

    /**
     * Make a salted SSHA512 password.
     *
     * @param string      $password
     * @param null|string $salt
     *
     * @return string
     */
    public static function ssha512($password, $salt = null)
    {
        return '{SSHA512}'.static::makeHash($password, 'hash', 'sha512', $salt ?? random_bytes(4));
    }

    /**
     * Make a non-salted SHA password.
     *
     * @param string $password
     *
     * @return string
     */
    public static function sha($password)
    {
        return '{SHA}'.static::makeHash($password, 'sha1');
    }

    /**
     * Make a non-salted SHA256 password.
     *
     * @param string $password
     *
     * @return string
     */
    public static function sha256($password)
    {
        return '{SHA256}'.static::makeHash($password, 'hash', 'sha256');
    }

    /**
     * Make a non-salted SHA384 password.
     *
     * @param string $password
     *
     * @return string
     */
    public static function sha384($password)
    {
        return '{SHA384}'.static::makeHash($password, 'hash', 'sha384');
    }

    /**
     * Make a non-salted SHA512 password.
     *
     * @param string $password
     *
     * @return string
     */
    public static function sha512($password)
    {
        return '{SHA512}'.static::makeHash($password, 'hash', 'sha512');
    }

    /**
     * Make a non-salted md5 password.
     *
     * @param string $password
     *
     * @return string
     */
    public static function md5($password)
    {
        return '{MD5}'.static::makeHash($password, 'md5');
    }

    /**
     * Crypt password with an MD5 salt.
     *
     * @param string $password
     * @param string $salt
     *
     * @return string
     */
    public static function md5Crypt($password, $salt = null)
    {
        return '{CRYPT}'.static::makeCrypt($password, static::CRYPT_SALT_TYPE_MD5, $salt);
    }

    /**
     * Crypt password with a SHA256 salt.
     *
     * @param string $password
     * @param string $salt
     *
     * @return string
     */
    public static function sha256Crypt($password, $salt = null)
    {
        return '{CRYPT}'.static::makeCrypt($password, static::CRYPT_SALT_TYPE_SHA256, $salt);
    }

    /**
     * Crypt a password with a SHA512 salt.
     *
     * @param string $password
     * @param string $salt
     *
     * @return string
     */
    public static function sha512Crypt($password, $salt = null)
    {
        return '{CRYPT}'.static::makeCrypt($password, static::CRYPT_SALT_TYPE_SHA512, $salt);
    }

    /**
     * Make a new password hash.
     *
     * @param string      $password The password to make a hash of.
     * @param string      $method   The hash function to use.
     * @param string|null $algo     The algorithm to use for hashing.
     * @param string|null $salt     The salt to append onto the hash.
     *
     * @return string
     */
    protected static function makeHash($password, $method, $algo = null, $salt = null)
    {
        $params = $algo ? [$algo, $password.$salt] : [$password.$salt];

        return base64_encode(pack('H*', call_user_func($method, ...$params)).$salt);
    }

    /**
     * Make a hashed password.
     *
     * @param string      $password
     * @param int         $type
     * @param null|string $salt
     *
     * @return string
     */
    protected static function makeCrypt($password, $type, $salt = null)
    {
        return crypt($password, $salt ?? static::makeCryptSalt($type));
    }

    /**
     * Make a salt for the crypt() method using the given type.
     *
     * @param int $type
     *
     * @return string
     */
    protected static function makeCryptSalt($type)
    {
        [$prefix, $length] = static::makeCryptPrefixAndLength($type);

        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        while (strlen($prefix) < $length) {
            $prefix .= substr($chars, random_int(0, strlen($chars) - 1), 1);
        }

        return $prefix;
    }

    /**
     * Determine the crypt prefix and length.
     *
     * @param int $type
     *
     * @return array
     *
     * @throws InvalidArgumentException
     */
    protected static function makeCryptPrefixAndLength($type)
    {
        switch ($type) {
            case static::CRYPT_SALT_TYPE_MD5:
                return ['$1$', 12];
            case static::CRYPT_SALT_TYPE_SHA256:
                return ['$5$', 16];
            case static::CRYPT_SALT_TYPE_SHA512:
                return ['$6$', 16];
            default:
                throw new InvalidArgumentException("Invalid crypt type [$type].");
        }
    }

    /**
     * Attempt to retrieve the hash method used for the password.
     *
     * @param string $password
     *
     * @return string|void
     */
    public static function getHashMethod($password)
    {
        if (! preg_match('/^\{(\w+)\}/', $password, $matches)) {
            return;
        }

        return $matches[1];
    }

    /**
     * Attempt to retrieve the hash method and algorithm used for the password.
     *
     * @param string $password
     *
     * @return array|void
     */
    public static function getHashMethodAndAlgo($password)
    {
        if (! preg_match('/^\{(\w+)\}\$([0-9a-z]{1})\$/', $password, $matches)) {
            return;
        }

        return [$matches[1], $matches[2]];
    }

    /**
     * Attempt to retrieve a salt from the encrypted password.
     *
     * @return string
     *
     * @throws LdapRecordException
     */
    public static function getSalt($encryptedPassword)
    {
        // crypt() methods.
        if (preg_match('/^\{(\w+)\}(\$.*\$).*$/', $encryptedPassword, $matches)) {
            return $matches[2];
        }

        // All other methods.
        if (preg_match('/{([^}]+)}(.*)/', $encryptedPassword, $matches)) {
            return substr(base64_decode($matches[2]), -4);
        }

        throw new LdapRecordException('Could not extract salt from encrypted password.');
    }

    /**
     * Determine if the hash method requires a salt to be given.
     *
     * @param string $method
     *
     * @return bool
     *
     * @throws \ReflectionException
     */
    public static function hashMethodRequiresSalt($method) : bool
    {
        $parameters = (new ReflectionMethod(static::class, $method))->getParameters();

        foreach ($parameters as $parameter) {
            if ($parameter->name === 'salt') {
                return true;
            }
        }

        return false;
    }
}
