# The OrganizationalUnit Model

The OrganizationalUnit model extends from the base `Adldap\Models\Model` class and contains
no specific methods / attributes that are limited to it.

## Creation

```php
// Adldap\Models\OrganizationalUnit
$ou = $provider->make()->ou([
    'name' => 'Workstation Computers',
]);

// Generate the OU's DN through the DN Builder:

$dn = $ou->getDnBuilder();

$dn->addOu('Workstation Computers');

$ou->setDn($dn);

// Or set the DN manually:

$ou->setDn('ou=Workstation Computers,dc=test,dc=local,dc=com');

$ou->save();
```
