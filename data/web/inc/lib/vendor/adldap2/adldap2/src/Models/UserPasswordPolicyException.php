<?php

namespace Adldap\Models;

use Adldap\AdldapException;

/**
 * Class UserPasswordPolicyException.
 *
 * Thrown when a users password is being changed but their new password
 * does not conform to the LDAP servers password policy.
 */
class UserPasswordPolicyException extends AdldapException
{
    //
}
