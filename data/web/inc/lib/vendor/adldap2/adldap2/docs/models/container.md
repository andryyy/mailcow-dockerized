# The Container Model

> **Note**: This model contains the trait `HasDescription` & `HasCriticalSystemObject`.
> For more information, visit the documentation:
> 
> [HasDescription](/models/traits/has-description.md),
> [HasCriticalSystemObject](/models/traits/has-critical-system-object.md),

## Creation

```php
// Adldap\Models\Container
$container = $provider->make()->container([
    'cn' => 'VPN Users',
]);
```

## Methods

The `Container` model contains only one unique method.

```php
$flags = $container->getSystemFlags();
```
