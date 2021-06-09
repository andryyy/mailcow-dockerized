# Requirements

Adldap2 requires the following:

- PHP 7.0 or greater
- LDAP extension enabled in PHP
- An LDAP server (ActiveDirectory, OpenLDAP, FreeIPA etc.)

# Composer

Adldap2 uses [Composer](https://getcomposer.org) for installation.

Once you have composer installed, run the following command in the root directory of your project:

```bash
composer require adldap2/adldap2
```

Then, if your application doesn't already require Composer's autoload, you will need to do it manually.

Insert this line at the top of your projects PHP script (usually `index.php`):

```php
require __DIR__ . '/vendor/autoload.php';
```

You're all set!

Now, head over to the [setup guide](setup.md) to get up and running.
