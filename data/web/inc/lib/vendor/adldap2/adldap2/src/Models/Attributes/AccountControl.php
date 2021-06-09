<?php

namespace Adldap\Models\Attributes;

use ReflectionClass;

/**
 * The Account Control class.
 *
 * This class is for easily building a user account control value.
 *
 * @link https://support.microsoft.com/en-us/kb/305144
 */
class AccountControl
{
    const SCRIPT = 1;

    const ACCOUNTDISABLE = 2;

    const HOMEDIR_REQUIRED = 8;

    const LOCKOUT = 16;

    const PASSWD_NOTREQD = 32;

    const ENCRYPTED_TEXT_PWD_ALLOWED = 128;

    const TEMP_DUPLICATE_ACCOUNT = 256;

    const NORMAL_ACCOUNT = 512;

    const INTERDOMAIN_TRUST_ACCOUNT = 2048;

    const WORKSTATION_TRUST_ACCOUNT = 4096;

    const SERVER_TRUST_ACCOUNT = 8192;

    const DONT_EXPIRE_PASSWORD = 65536;

    const MNS_LOGON_ACCOUNT = 131072;

    const SMARTCARD_REQUIRED = 262144;

    const TRUSTED_FOR_DELEGATION = 524288;

    const NOT_DELEGATED = 1048576;

    const USE_DES_KEY_ONLY = 2097152;

    const DONT_REQ_PREAUTH = 4194304;

    const PASSWORD_EXPIRED = 8388608;

    const TRUSTED_TO_AUTH_FOR_DELEGATION = 16777216;

    const PARTIAL_SECRETS_ACCOUNT = 67108864;

    /**
     * Stores the values to be added together to
     * build the user account control integer.
     *
     * @var array
     */
    protected $values = [];

    /**
     * Constructor.
     *
     * @param int $flag
     */
    public function __construct($flag = null)
    {
        if (!is_null($flag)) {
            $this->apply($flag);
        }
    }

    /**
     * Get the value when casted to string.
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getValue();
    }

    /**
     * Get the value when casted to int.
     *
     * @return int
     */
    public function __toInt()
    {
        return $this->getValue();
    }

    /**
     * Add the value to the account control values.
     *
     * @param int $value
     *
     * @return AccountControl
     */
    public function add($value)
    {
        // Use the value as a key so if the same value
        // is used, it will always be overwritten
        $this->values[$value] = $value;

        return $this;
    }

    /**
     * Remove the value from the account control.
     *
     * @param int $value
     *
     * @return $this
     */
    public function remove($value)
    {
        unset($this->values[$value]);

        return $this;
    }

    /**
     * Extract and apply the flag.
     *
     * @param int $flag
     */
    public function apply($flag)
    {
        $this->setValues($this->extractFlags($flag));
    }

    /**
     * Determine if the current AccountControl object contains the given UAC flag(s).
     *
     * @param int $flag
     *
     * @return bool
     */
    public function has($flag)
    {
        // We'll extract the given flag into an array of possible flags, and
        // see if our AccountControl object contains any of them.
        $flagsUsed = array_intersect($this->extractFlags($flag), $this->values);

        return in_array($flag, $flagsUsed);
    }

    /**
     * The logon script will be run.
     *
     * @return AccountControl
     */
    public function runLoginScript()
    {
        return $this->add(static::SCRIPT);
    }

    /**
     * The user account is locked.
     *
     * @return AccountControl
     */
    public function accountIsLocked()
    {
        return $this->add(static::LOCKOUT);
    }

    /**
     * The user account is disabled.
     *
     * @return AccountControl
     */
    public function accountIsDisabled()
    {
        return $this->add(static::ACCOUNTDISABLE);
    }

    /**
     * This is an account for users whose primary account is in another domain.
     *
     * This account provides user access to this domain, but not to any domain that
     * trusts this domain. This is sometimes referred to as a local user account.
     *
     * @return AccountControl
     */
    public function accountIsTemporary()
    {
        return $this->add(static::TEMP_DUPLICATE_ACCOUNT);
    }

    /**
     * This is a default account type that represents a typical user.
     *
     * @return AccountControl
     */
    public function accountIsNormal()
    {
        return $this->add(static::NORMAL_ACCOUNT);
    }

    /**
     * This is a permit to trust an account for a system domain that trusts other domains.
     *
     * @return AccountControl
     */
    public function accountIsForInterdomain()
    {
        return $this->add(static::INTERDOMAIN_TRUST_ACCOUNT);
    }

    /**
     * This is a computer account for a computer that is running Microsoft
     * Windows NT 4.0 Workstation, Microsoft Windows NT 4.0 Server, Microsoft
     * Windows 2000 Professional, or Windows 2000 Server and is a member of this domain.
     *
     * @return AccountControl
     */
    public function accountIsForWorkstation()
    {
        return $this->add(static::WORKSTATION_TRUST_ACCOUNT);
    }

    /**
     * This is a computer account for a domain controller that is a member of this domain.
     *
     * @return AccountControl
     */
    public function accountIsForServer()
    {
        return $this->add(static::SERVER_TRUST_ACCOUNT);
    }

    /**
     * This is an MNS logon account.
     *
     * @return AccountControl
     */
    public function accountIsMnsLogon()
    {
        return $this->add(static::MNS_LOGON_ACCOUNT);
    }

    /**
     * (Windows 2000/Windows Server 2003) This account does
     * not require Kerberos pre-authentication for logging on.
     *
     * @return AccountControl
     */
    public function accountDoesNotRequirePreAuth()
    {
        return $this->add(static::DONT_REQ_PREAUTH);
    }

    /**
     * When this flag is set, it forces the user to log on by using a smart card.
     *
     * @return AccountControl
     */
    public function accountRequiresSmartCard()
    {
        return $this->add(static::SMARTCARD_REQUIRED);
    }

    /**
     * (Windows Server 2008/Windows Server 2008 R2) The account is a read-only domain controller (RODC).
     *
     * This is a security-sensitive setting. Removing this setting from an RODC compromises security on that server.
     *
     * @return AccountControl
     */
    public function accountIsReadOnly()
    {
        return $this->add(static::PARTIAL_SECRETS_ACCOUNT);
    }

    /**
     * The home folder is required.
     *
     * @return AccountControl
     */
    public function homeFolderIsRequired()
    {
        return $this->add(static::HOMEDIR_REQUIRED);
    }

    /**
     * No password is required.
     *
     * @return AccountControl
     */
    public function passwordIsNotRequired()
    {
        return $this->add(static::PASSWD_NOTREQD);
    }

    /**
     * The user cannot change the password. This is a permission on the user's object.
     *
     * For information about how to programmatically set this permission, visit the following link:
     *
     * @link http://msdn2.microsoft.com/en-us/library/aa746398.aspx
     *
     * @return AccountControl
     */
    public function passwordCannotBeChanged()
    {
        return $this->add(static::PASSWD_NOTREQD);
    }

    /**
     * Represents the password, which should never expire on the account.
     *
     * @return AccountControl
     */
    public function passwordDoesNotExpire()
    {
        return $this->add(static::DONT_EXPIRE_PASSWORD);
    }

    /**
     * (Windows 2000/Windows Server 2003) The user's password has expired.
     *
     * @return AccountControl
     */
    public function passwordIsExpired()
    {
        return $this->add(static::PASSWORD_EXPIRED);
    }

    /**
     * The user can send an encrypted password.
     *
     * @return AccountControl
     */
    public function allowEncryptedTextPassword()
    {
        return $this->add(static::ENCRYPTED_TEXT_PWD_ALLOWED);
    }

    /**
     * When this flag is set, the service account (the user or computer account)
     * under which a service runs is trusted for Kerberos delegation.
     *
     * Any such service can impersonate a client requesting the service.
     *
     * To enable a service for Kerberos delegation, you must set this
     * flag on the userAccountControl property of the service account.
     *
     * @return AccountControl
     */
    public function trustForDelegation()
    {
        return $this->add(static::TRUSTED_FOR_DELEGATION);
    }

    /**
     * (Windows 2000/Windows Server 2003) The account is enabled for delegation.
     *
     * This is a security-sensitive setting. Accounts that have this option enabled
     * should be tightly controlled. This setting lets a service that runs under the
     * account assume a client's identity and authenticate as that user to other remote
     * servers on the network.
     *
     * @return AccountControl
     */
    public function trustToAuthForDelegation()
    {
        return $this->add(static::TRUSTED_TO_AUTH_FOR_DELEGATION);
    }

    /**
     * When this flag is set, the security context of the user is not delegated to a
     * service even if the service account is set as trusted for Kerberos delegation.
     *
     * @return AccountControl
     */
    public function doNotTrustForDelegation()
    {
        return $this->add(static::NOT_DELEGATED);
    }

    /**
     * (Windows 2000/Windows Server 2003) Restrict this principal to
     * use only Data Encryption Standard (DES) encryption types for keys.
     *
     * @return AccountControl
     */
    public function useDesKeyOnly()
    {
        return $this->add(static::USE_DES_KEY_ONLY);
    }

    /**
     * Get the account control value.
     *
     * @return int
     */
    public function getValue()
    {
        return array_sum($this->values);
    }

    /**
     * Get the account control flag values.
     *
     * @return array
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * Set the account control values.
     *
     * @param array $flags
     */
    public function setValues(array $flags)
    {
        $this->values = $flags;
    }

    /**
     * Get all possible account control flags.
     *
     * @return array
     */
    public function getAllFlags()
    {
        return (new ReflectionClass(__CLASS__))->getConstants();
    }

    /**
     * Extracts the given flag into an array of flags used.
     *
     * @param int $flag
     *
     * @return array
     */
    public function extractFlags($flag)
    {
        $flags = [];

        for ($i = 0; $i <= 26; $i++) {
            if ((int) $flag & (1 << $i)) {
                $flags[1 << $i] = 1 << $i;
            }
        }

        return $flags;
    }
}
