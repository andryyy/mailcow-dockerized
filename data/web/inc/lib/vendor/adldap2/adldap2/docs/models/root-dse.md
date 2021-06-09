# The RootDse Model

## Getting the Root DSE

To get the Root DSE of your LDAP server, call the `getRootDse()` method off a new search:

```php
$rootDse = $provider->search()->getRootDse();
```

## Getting the schema naming context

To get the Root DSE schema naming context, call the `getSchemaNamingContext()`:

```php
$rootDse = $provider->search()->getRootDse();

$context = $rootDse->getSchemaNamingContext();

// Returns 'cn=Schema,cn=Configuration,dc=corp,dc=acme,dc=org'
echo $context;
```

## Getting the root domain naming context

To get the Root DSE domain naming context, call the `getRootDomainNamingContext()`:

```php
$context = $rootDse->getRootDomainNamingContext();

// Returns 'dc=corp,dc=acme,dc=org'
echo $context;
```
