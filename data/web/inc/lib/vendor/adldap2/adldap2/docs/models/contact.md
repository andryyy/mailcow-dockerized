# The Contact Model

The Contact model extends from the base `Adldap\Models\Model` class and contains
no specific methods / attributes that are limited to it.

## Creation

```php
// Adldap\Models\Contact
$contact = $provider->make()->contact([
    'cn' => 'Suzy Doe',
]);
```
