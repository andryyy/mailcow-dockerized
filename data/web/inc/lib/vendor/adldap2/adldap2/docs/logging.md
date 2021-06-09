# Logging

Adldap2 includes an implementation of PSR's widely supported [Logger](https://github.com/php-fig/log) interface.

By default, all of Adldap2's [events](events.md) will call the logger you have set to utilize.

> **Note**: Adldap2 does not include a file / text logger. You must implement your own.

## Registering & Enabling a Logger

To register a logger call `Adldap::setLogger()`. The logger must implement the `Psr\Log\LoggerInterface`.

>**Note**: Be sure to set the logger prior to creating a new `Adldap` instance. This
> ensures all events throughout the lifecycle of the request use your logger.

```php
use Adldap\Adldap;

Adldap::setLogger($myLogger);

$config = ['...'];

$ad = new Adldap();

$ad->addProvider($config);
```

## Disabling Logging

If you need to disable the event logger after a certain set of operations, simply pass in `null` and logging will be disabled:

```php
use Adldap\Adldap;

Adldap::setLogger($myLogger);

$config = ['...'];

$ad = new Adldap();

$ad->addProvider($config);

try {
    $ad->connect();
    
    // Disable logging anything else.
    Adldap::setLogger(null);
} catch (\Adldap\Connections\BindException $e) {
    //
}
```

## Logged Information

Here is a list of events that are logged along with the information included:

| Authentication Events | Logged |
|---|---|
| `Adldap\Auth\Events\Attempting` | `LDAP (ldap://192.168.1.1:389) - Operation: Adldap\Auth\Events\Attempting - Username: CN=Steve Bauman,OU=Users,DC=corp,DC=acme,DC=org` | 
| `Adldap\Auth\Events\Binding` |` LDAP (ldap://192.168.1.1:389) - Operation: Adldap\Auth\Events\Binding - Username: CN=Steve Bauman,OU=Users,DC=corp,DC=acme,DC=org` | 
| `Adldap\Auth\Events\Bound` | `LDAP (ldap://192.168.1.1:389) - Operation: Adldap\Auth\Events\Bound - Username: CN=Steve Bauman,OU=Users,DC=corp,DC=acme,DC=org` | 
| `Adldap\Auth\Events\Passed` | `LDAP (ldap://192.168.1.1:389) - Operation: Adldap\Auth\Events\Passed - Username: CN=Steve Bauman,OU=Users,DC=corp,DC=acme,DC=org` | 
| `Adldap\Auth\Events\Failed` | `LDAP (ldap://192.168.1.1:389) - Operation: Adldap\Auth\Events\Failed - Username: CN=Steve Bauman,OU=Users,DC=corp,DC=acme,DC=org - Result: Invalid Credentials` |

| Model Events | Logged |
|---|---|
| `Adldap\Models\Events\Saving` | `LDAP (ldap://192.168.1.1:389) - Operation: Saving - On: Adldap\Models\User - Distinguished Name: cn=John Doe,dc=acme,dc=org` | 
| `Adldap\Models\Events\Saved` | `LDAP (ldap://192.168.1.1:389) - Operation: Saved - On: Adldap\Models\User - Distinguished Name: cn=John Doe,dc=acme,dc=org` | 
| `Adldap\Models\Events\Creating` | `LDAP (ldap://192.168.1.1:389) - Operation: Creating - On: Adldap\Models\User - Distinguished Name: cn=John Doe,dc=acme,dc=org` | 
| `Adldap\Models\Events\Created` | `LDAP (ldap://192.168.1.1:389) - Operation: Created - On: Adldap\Models\User - Distinguished Name: cn=John Doe,dc=acme,dc=org` | 
| `Adldap\Models\Events\Updating` | `LDAP (ldap://192.168.1.1:389) - Operation: Updating - On: Adldap\Models\User - Distinguished Name: cn=John Doe,dc=acme,dc=org` | 
| `Adldap\Models\Events\Updated` | `LDAP (ldap://192.168.1.1:389) - Operation: Updated - On: Adldap\Models\User - Distinguished Name: cn=John Doe,dc=acme,dc=org` | 
| `Adldap\Models\Events\Deleting` | `LDAP (ldap://192.168.1.1:389) - Operation: Deleting - On: Adldap\Models\User - Distinguished Name: cn=John Doe,dc=acme,dc=org` | 
| `Adldap\Models\Events\Deleted` | `LDAP (ldap://192.168.1.1:389) - Operation: Deleted - On: Adldap\Models\User - Distinguished Name: cn=John Doe,dc=acme,dc=org` | 
