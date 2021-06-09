# Troubleshooting

#### Creating and Setting a Users Password

To set a users password when you've created a new one, you need to enable their account, **then** set their password.

For example:

```php
// Construct a new user instance.
$user = $provider->make()->user();

// Set the user profile details.
$user->setAccountName('jdoe');
$user->setFirstName('John');
$user->setLastName('Doe');
$user->setCompany('ACME');
$user->setEmail('jdoe@acme.com');

// Save the new user.
if ($user->save()) {
    // Enable the new user (using user account control).
    $user->setUserAccountControl(512);

    // Set new user password
    $user->setPassword('Password123');

    // Save the user.
    if($user->save()) {
        // The password was saved successfully.
    }
}
```

#### Determining and Troubleshooting a Binding Failure

> **Note**: The below guide is using ActiveDirectory. Your mileage will vary using other LDAP distributions.

To determine the reason why a bind attempt failed, you can use the event dispatcher to listen for
the `Failed` event, and retrieve the errors that were returned from your LDAP server:

```php
use Adldap\Adldap;
use Adldap\Auth\Events\Failed;

$d = Adldap::getEventDispatcher();

$d->listen(Failed::class, function (Failed $event) {
    $conn = $event->connection;
    
    echo $conn->getLastError(); // 'Invalid credentials'
    echo $conn->getDiagnosticMessage(); // '80090308: LdapErr: DSID-0C09042A, comment: AcceptSecurityContext error, data 532, v3839'
    
    if ($error = $conn->getDetailedError()) {
        $error->getErrorCode(); // 49
        $error->getErrorMessage(); // 'Invalid credentials'
        $error->getDiagnosticMessage(); // '80090308: LdapErr: DSID-0C09042A, comment: AcceptSecurityContext error, data 532, v3839'
    }
});
```

The above diagnostic message can be parsed down further if needed. The error code after the 'data' string
in the above message indicates several things about the bind failure. Here is a list:

- 525 - user not found
- 52e - invalid credentials
- 530 - not permitted to logon at this time
- 531 - not permitted to logon at this workstation
- 532 - password expired
- 533 - account disabled
- 701 - account expired
- 773 - user must reset password
- 775 - user account locked

From the example above, you can see that the authenticating account has their password expired, due to "532" error code.

#### Retrieving All Records Inside a Group

To retrieve all records inside a particular group (including nested groups), use the `rawFilter()` method:

```php
// The `memberof:1.2.840.113556.1.4.1941:` string indicates
// that we want all nested group records as well.
$filter = '(memberof:1.2.840.113556.1.4.1941:=CN=MyGroup,DC=example,DC=com)';

$users = $provider->search()->rawFilter($filter)->get();
```

#### I'm connected but not getting any search results!

The first thing you need to ensure is your `base_dn` in your configuration.

Your `base_dn` needs to identical to the base DN on your domain. Even one mistyped character will result in no search results.

If you also include an `ou` in your base DN (ex. `ou=Accounting,dc=corp,dc=acme,dc=org`), you will only receive results inside the `Accounting` OU.

Once you're connected to your LDAP server, retrieve the Root DSE record.

Here's a full example:

```php
$providers = [
    'default' => [
        'base_dn' => '',
        '...',
    ]
];

$ad = new Adldap\Adldap($providers);

try {
    $provider = $ad->connect();
    
    $root = $provider->search()->getRootDse();
    
    // ex. Returns 'dc=corp,dc=acme,dc=org'
    die($root->getRootDomainNamingContext());

} catch (Adldap\Auth\BindException $e) {
    //
}
```
