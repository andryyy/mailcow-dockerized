# The Organization Model

The Organization model extends from the base `Adldap\Models\Model` class and contains
no specific methods / attributes that are limited to it.

## Creation

```php
// Adldap\Models\Organization
$org = $provider->make()->organization([
    'o' => 'Some Company',
]);

// Set the DN manually:

$org->setDn('o=Some Company,dc=test,dc=local,dc=com');

$org->save();
```
