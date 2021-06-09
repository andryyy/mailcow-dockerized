# Setup

## Configuration

To configure your LDAP connections, you can use two methods:

1. Using an array
2. Using a `Adldap\Configuration\DomainConfiguration` object

Either or will produce the same results. Use whichever you feel most comfortable with.

### Using an array

```php
$config = [
    'hosts' => [
        'DC-01.corp.acme.org',
    ],
    '...'
];
```

### Using a `DomainConfiguration` object

```php
// Setting options via first argument:
$config = new Adldap\Configuration\DomainConfiguration([
    'hosts' => [
        'DC-01.corp.acme.org',
    ],
]);

// Setting via the `set()` method:
$config->set('hosts', [
    'DC-01.corp.acme.org',
]);
```

### Options

#### Array Example With All Options

```php
// Create the configuration array.
$config = [
    // Mandatory Configuration Options
    'hosts'            => ['corp-dc1.corp.acme.org', 'corp-dc2.corp.acme.org'],
    'base_dn'          => 'dc=corp,dc=acme,dc=org',
    'username'         => 'admin',
    'password'         => 'password',

    // Optional Configuration Options
    'schema'           => Adldap\Schemas\ActiveDirectory::class,
    'account_prefix'   => 'ACME-',
    'account_suffix'   => '@acme.org',
    'port'             => 389,
    'follow_referrals' => false,
    'use_ssl'          => false,
    'use_tls'          => false,
    'version'          => 3,
    'timeout'          => 5,

    // Custom LDAP Options
    'custom_options'   => [
        // See: http://php.net/ldap_set_option
        LDAP_OPT_X_TLS_REQUIRE_CERT => LDAP_OPT_X_TLS_HARD
    ]
];
```

#### Required Options

##### Hosts

The hosts option is an array of IP addresses or hostnames located
on your network that serve Active Directory.

You insert as many servers or as little as you'd like depending on your forest (with the minimum of one of course).

> **Note:** Do not append your port to your IP addresses or hostnames. Use the `port` configuration option instead.

##### Base Distinguished Name

The base distinguished name is the base distinguished name you'd like to perform operations on.

An example base DN would be `DC=corp,DC=acme,DC=org`.

If one is not defined, you will not retrieve any search results.

> **Note**: Your base DN is **case insensitive**. You do not need to worry about incorrect casing.

##### Username & Password

To connect to your LDAP server, a username and password is required to be able to query and run operations on your server(s).

You can use any account that has these permissions.

> **Note**: To run administration level operations, such as resetting passwords,
> this account **must** have permissions to do so on your directory.

#### Optional Options

##### Schema

The schema option allows you to configure which directory you're connecting to.

This is a somewhat optional, however this **must** be changed if you're connecting
to an alternate LDAP variant such as OpenLDAP or FreeIPA.

Below are available schemas:

- `Adldap\Schemas\ActiveDirectory`
- `Adldap\Schemas\OpenLDAP`
- `Adldap\Schemas\FreeIPA`

By default, this option is set to the `Adldap\Schemas\ActiveDirectory` schema.

##### Account Prefix

The account prefix option is a string to *prepend* to all usernames that go through the `Guard::attempt()` method.

This option is just for convenience.

It is usually not needed (if utilizing the account suffix), however the functionality is
in place if you would like to only allow certain users with the specified prefix
to login, or add a domain so your users do not have to specify one.

##### Account Suffix

The account suffix option is a string to *append* to all usernames that go
through the `Adldap\Auth\Guard::attempt()` method.

This option is just for convenience.

An example use case for this would be inserting your LDAP users `userPrincipalName` suffix so you don't need to append it manually.

For example, with a `account_suffix` in your configuration set to `@corp.acme.org`:

```php
$username = 'jdoe';
$password = 'password';

// Here, an `ldap_bind()` will be called with a username of 'jdoe@corp.acme.org`
$provider->auth()->attempt($username, $password);
```

##### Port

The port option is used for authenticating and binding to your LDAP server.

The default ports are already used for non SSL and SSL connections (389 and 636).

Only insert a port if your LDAP server uses a unique port.

##### Follow Referrals

The follow referrals option is a boolean to tell active directory to follow a referral to another server on your network if the server queried knows the information your asking for exists, but does not yet contain a copy of it locally.

This option is defaulted to false.

Disable this option if you're experiencing search / connectivity issues.

For more information, visit: https://technet.microsoft.com/en-us/library/cc978014.aspx

##### SSL & TLS

These Boolean options enable an SSL or TLS connection to your LDAP server.

Only **one** can be set to `true`. You must chose either or.

> **Note**: You **must** enable SSL or TLS to reset passwords in ActiveDirectory.

These options are definitely recommended if you have the ability to connect to your server securely.

> **Note**: TLS is recommended over SSL, as SSL is now labelled as a depreciated mechanism for securely running LDAP operations.

##### Version

The LDAP version to use for your connection.

Must be an integer and can either be `2` or `3`.

##### Timeout

The timeout option allows you to configure the amount of seconds to wait until
your application receives a response from your LDAP server.

The default is 5 seconds.

##### Custom Options

Arbitrary options can be set for the connection to fine-tune TLS and connection behavior.

Please note that `LDAP_OPT_PROTOCOL_VERSION`, `LDAP_OPT_NETWORK_TIMEOUT` and `LDAP_OPT_REFERRALS` will be ignored if set.

These are set above with the `version`, `timeout` and `follow_referrals` keys respectively.

Valid options are listed in the [PHP documentation for ldap_set_option](http://php.net/ldap_set_option).

## Getting Started

Each LDAP connection you have will be contained inside the `Adldap` instance as its own **connection provider**.

There are a couple of ways you can easily add each of your LDAP connections. Let's walk through them:

**Using a configuration array:**
```php
$config = ['...'];

$ad = new Adldap\Adldap();

$ad->addProvider($config);

// You can also specify the name of the
// connection as the second argument:
$ad->addProvider($config, 'connection-one');
```

**Using a DomainConfiguration object:**
```php
$ad = new Adldap\Adldap();

$config = new Adldap\Configuration\DomainConfiguration(['...']);

$ad->addProvider($config, 'connection-one');
```

**Using the constructor:**

> **Note**: When inserting your configuration into a new `Adldap` instance, you
> need to set a key for each connection. **This will be its connection name**.

```php
$connections = [
    'connection1' => [
        'hosts' => ['...'],
    ],
    'connection2' => [
        'hosts' => ['...'],
    ],
];

$ad = new Adldap\Adldap($connections);
```

## Connecting

The easiest way to get connected is to call the `connect($name)` method on your `Adldap` instance.

Its first argument accepts the name of your configured connection.

This method will return you a connected **connection provider** when
successful, and throw an exception when unsuccessful:

```php
$ad = new Adldap\Adldap();

$config = ['...'];

$connectionName = 'my-connection';

$ad->addProvider($config, $connectionName);

try {
    $provider = $ad->connect($connectionName);

    // Great, we're connected!
} catch (Adldap\Auth\BindException $e) {
    // Failed to connect.
}
```

### Using an alternate username / password

If you'd like to connect to your configured connection using a different username and password than your configuration, then simply provide them in the second and third arguments:

```php
$username = 'server-admin';
$password = 'my-super-secret-password';

$provider = $ad->connect($connectionName, $username, $password);
```

### Dynamically Connecting

If you're like me and like chainable (fluent) API's in PHP, then dynamically connecting is a nice option to have.

To dynamically connect, simply call any connection provider method on your `Adldap` instance.

> **Note**: Your default connection will be used when dynamically connecting.
> More on this below.

Here's an example:

```php
$ad = new Adldap\Adldap();

$ad->addProvider($config = ['...']);

try {
    $users = $ad->search()->users()->get();
} catch (Adldap\Auth\BindException $e) {
    // Failed to connect.
}
```

### Anonymously Binding

If you'd like to anonymously bind, set your `username` and `password` configuration to `null`:

```php
$ad = new Adldap\Adldap();

$config = [
    'username' => null,
    'password' => null,
];

$ad->addProvider($config);

try {
    $provider = $ad->connect();

    // ...
} catch (BindException $e) {
    // Failed.
}
```

Or, manually bind your provider and don't pass in a `username` or `password` parameter:

```php
$config = [
    'hosts' => ['...'],
];

$ad->addProvider($config);

$provider = $ad->getDefaultProvider();

try {
    $provider->auth()->bind();

    // Successfully bound.
} catch (BindException $e) {
    // Failed.
}
```

### Setting a Default Connection

Setting a default LDAP connection is used for dynamically connecting.

To set your default connection, call the `setDefaultProvider($name)` method:

```php
$ad->setDefaultProvider('my-connection');

$computers = $ad->search()->computers()->get();
```

## Authenticating

If you're looking to authenticate (bind) users using your LDAP connection, call
the `auth()->attempt()` method on your provider instance:

```php
$username = 'jdoe';
$password = 'Password@1';

try {
    if ($provider->auth()->attempt($username, $password)) {
        // Passed.
    } else {
        // Failed.
    }
} catch (Adldap\Auth\UsernameRequiredException $e) {
    // The user didn't supply a username.
} catch (Adldap\Auth\PasswordRequiredException $e) {
    // The user didn't supply a password.
}
```

If you'd like all LDAP operations during the same request to be ran under the
authenticated user, pass in `true` into the last paramter:

```php
if ($provider->auth()->attempt($username, $password, $bindAsUser = true)) {
    // Passed.
} else {
    // Failed.
}
```

---

Now that you've learned the basics of configuration and
getting yourself connected, continue on to learn
[how to search your LDAP directory](searching.md).

## Using Other LDAP Servers (OpenLDAP / FreeIPA / etc.)

Alternate LDAP server variants such as OpenLDAP or FreeIPA contain
some different attribute names than ActiveDirectory.

The Adldap2 schema offers an attribute map for each available LDAP attribute, and
is completely configurable and customizable.

If you're using an alternate LDAP server variant such as OpenLDAP or FreeIPA, you **must** change the default schema inside your configuration array. If you do not, you won't receive the correct model instances for results, and you won't be
able to utilize some standard methods available on these models.

By default, Adldap2 is configured to be used with **Microsoft ActiveDirectory**.

When creating your configuration array, set your schema using the `schema` key:


**Using configuration array:**
```php
$ad = new Adldap\Adldap();

$config = [
    '...',
    'schema' => Adldap\Schemas\OpenLDAP::class
];

$ad->addProvider($config);
```

**Using configuration object:**
```php
$ad = new Adldap\Adldap();

$config = new Adldap\Configuration\DomainConfiguration();

$config->set('schema', Adldap\Schemas\OpenLDAP::class);

$ad->addProvider($config);
```

Once you've set the schema of your connection provider, you can use the same API interacting with different LDAP servers.

Continue onto the [searching](searching.md) documentation to learn how to begin querying your LDAP server(s).

## Using G-Suite Secure LDAP Service

G-Suite LDAP service only uses client certificates and no username + password, make sure yo match base_dn with your domian.

```php
$ad = new \Adldap\Adldap();

// Create a configuration array.
$config = [  
    'hosts'    => ['ldap.google.com'],
    'base_dn'  => 'dc=your-domain,dc=com',
    'use_tls' => true,
    'version' => 3,
    'schema' => Adldap\Schemas\GSuite::class,
    'custom_options' => [
        LDAP_OPT_X_TLS_CERTFILE => 'Google_2023_02_05_35779.crt',
        LDAP_OPT_X_TLS_KEYFILE => 'Google_2023_02_05_35779.key', 
    ]
];

$ad->addProvider($config);

try {
    $provider = $ad->connect();
    
    $results = $provider->search()->ous()->get();
    
    echo 'OUs:'."\r\n";
    echo '==============='."\r\n";
    foreach($results as $ou) {
        echo $ou->getDn()."\r\n";
    }
    
    echo "\r\n";
    
    $results = $provider->search()->users()->get();
    
    echo 'Users:'."\r\n";
    echo '==============='."\r\n";
    foreach($results as $user) {
        
        echo $user->getAccountName()."\r\n";
    }
    
    echo "\r\n";
    
    $results = $provider->search()->groups()->get();
    
    echo 'Groups:'."\r\n";
    echo '==============='."\r\n";
    foreach($results as $group) {
        echo $group->getCommonName().' | '.$group->getDisplayName()."\r\n";
    }

} catch (\Adldap\Auth\BindException $e) {

    echo 'Error: '.$e->getMessage()."\r\n";
}
```

## Raw Operations

### Introduction

If you want to connect to your LDAP server without utilizing Adldap's models (old fashion way), and want to get back the data in a raw format you can easily do so.

If you call `getConnection()` on your connected provider instance, you can perform all LDAP functions on a container class that encapsulates all of PHP's LDAP methods.

You can view all methods avaialble by browsing the LDAP class [here](https://github.com/Adldap2/Adldap2/blob/master/src/Connections/Ldap.php).

Now for some examples:

### Examples

```php
$ad = new Adldap\Adldap();

$config = ['...'];

$ad->addProvider($config);

$provider = $ad->connect();

$rawConnection = $provider->getConnection();

// Performing a raw search.
$result = $rawConnection->search($basedn = 'dc=corp,dc=acme,dc=org', $filter = "cn=johndoe", $selectedAttributes = ['cn', 'department']);

$dn = "cn=John Smith,ou=Wizards,dc=example,dc=com";

// Adding a new LDAP record.
$result = $rawConnection->add($dn, $entry);

// Batch modifying an LDAP record.
$modifs = [
    [
        "attrib"  => "telephoneNumber",
        "modtype" => LDAP_MODIFY_BATCH_ADD,
        "values"  => ["+1 555 555 1717"],
    ],
];

$result = $rawConnection->modifyBatch($dn, $modifs);

// Deleting an LDAP record.
$result = $rawConnection->delete($dn);

// .. etc
```
