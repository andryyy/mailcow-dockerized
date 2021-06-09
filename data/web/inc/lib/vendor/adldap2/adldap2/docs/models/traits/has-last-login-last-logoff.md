# HasLastLoginAndLastLogoff Trait

Models that contain this trait have the `lastlogoff`, `lastlogon` and `lastlogontimestamp` attributes.

## Methods

```php
// Returns the models's last log off attribute.
$computer->getLastLogOff();

//  Returns the models's last log on attribute.
$computer->getLastLogon();

// Returns the models's last log on timestamp attribute.
$computer->getLastLogonTimestamp();
```
