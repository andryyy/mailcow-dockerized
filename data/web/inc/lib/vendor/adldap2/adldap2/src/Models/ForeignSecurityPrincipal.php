<?php

namespace Adldap\Models;

/**
 * Class ForeignSecurityPrincipal.
 *
 * Represents an LDAP ForeignSecurityPrincipal.
 */
class ForeignSecurityPrincipal extends Entry
{
    use Concerns\HasMemberOf;
}
