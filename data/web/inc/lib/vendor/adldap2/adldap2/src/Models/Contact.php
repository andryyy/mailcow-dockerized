<?php

namespace Adldap\Models;

/**
 * Class Contact.
 *
 * Represents an LDAP contact.
 */
class Contact extends Entry
{
    use Concerns\HasMemberOf;
    use Concerns\HasUserProperties;
}
