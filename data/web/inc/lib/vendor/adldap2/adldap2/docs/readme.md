# Introduction

## What is Adldap2?

Adldap2 is a PHP LDAP package that allows you to:

1. Easily manage multiple LDAP connections at once
2. Perform authentication
3. Search your LDAP directory with a fluent and easy to use query builder
4. Create / Update / Delete LDAP entities with ease
5. And more

## History of Adldap2

Adldap2 was originally created as a fork of the original LDAP library [adLDAP](https://github.com/adldap/adLDAP) due to bugs, and it being completely abandoned.

Adldap2 contains absolutely no similarities to the original repository, and was built to be as easily accessible as possible, with great documentation, and easily understandable syntax.

Much of the API was constructed with Ruby's ActiveRecord and Laravel's Eloquent in mind, and to be an answer to the question:

> _Why can't we use LDAP like we use a database?_

## Why should you use Adldap2?

Working with LDAP in PHP can be a messy and confusing endeavor, especially when using multiple connections, creating and managing entities, performing moves, resetting passwords, and performing ACL modifications to user accounts.

Wrapper classes for LDAP are usually always created in PHP applications.

Adldap2 allows you to easily manage the above problems without reinventing the wheel for every project.

## Implementations

- [Laravel](https://github.com/Adldap2/Adldap2-Laravel)

## Quick Start

Install the package via `composer`:

```
composer require adldap2/adldap2
```

Use Adldap2:

```php
// Construct new Adldap instance.
$ad = new \Adldap\Adldap();

// Create a configuration array.
$config = [  
  // An array of your LDAP hosts. You can use either
  // the host name or the IP address of your host.
  'hosts'    => ['ACME-DC01.corp.acme.org', '192.168.1.1'],

  // The base distinguished name of your domain to perform searches upon.
  'base_dn'  => 'dc=corp,dc=acme,dc=org',

  // The account to use for querying / modifying LDAP records. This
  // does not need to be an admin account. This can also
  // be a full distinguished name of the user account.
  'username' => 'admin@corp.acme.org',
  'password' => 'password',
];

// Add a connection provider to Adldap.
$ad->addProvider($config);

try {
    // If a successful connection is made to your server, the provider will be returned.
    $provider = $ad->connect();

    // Performing a query.
    $results = $provider->search()->where('cn', '=', 'John Doe')->get();

    // Finding a record.
    $user = $provider->search()->find('jdoe');

    // Creating a new LDAP entry. You can pass in attributes into the make methods.
    $user =  $provider->make()->user([
        'cn'          => 'John Doe',
        'title'       => 'Accountant',
        'description' => 'User Account',
    ]);

    // Setting a model's attribute.
    $user->cn = 'John Doe';

    // Saving the changes to your LDAP server.
    if ($user->save()) {
        // User was saved!
    }
} catch (\Adldap\Auth\BindException $e) {

    // There was an issue binding / connecting to the server.

}
```

## Versioning

Adldap2 is versioned under the [Semantic Versioning](http://semver.org/) guidelines as much as possible.

Releases will be numbered with the following format:

`<major>.<minor>.<patch>`

And constructed with the following guidelines:

* Breaking backward compatibility bumps the major and resets the minor and patch.
* New additions without breaking backward compatibility bumps the minor and resets the patch.
* Bug fixes and misc changes bumps the patch.

Minor versions are not maintained individually, and you're encouraged to upgrade through to the next minor version.

Major versions are maintained individually through separate branches.
