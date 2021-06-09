# The Computer Model

> **Note**: This model contains the traits `HasDescription`, `HasLastLogonAndLogOff` & `HasCriticalSystemObject`.
> For more information, visit the documentation:
>
> [HasDescription](/models/traits/has-description.md),
> [HasLastLogonAndLogOff](/models/traits/has-last-login-last-logoff.md),
> [HasCriticalSystemObject](/models/traits/has-critical-system-object.md)

## Methods

```php
$computer = $provider->search()->computers()->find('ACME-EXCHANGE');

// Returns 'Windows Server 2003'
$computer->getOperatingSystem();

// Returns '5.2 (3790)';
$computer->getOperatingSystemVersion();

// Returns 'Service Pack 1';
$computer->getOperatingSystemServicePack();

// Returns 'ACME-DESKTOP001.corp.acme.org'
$computer->getDnsHostName();

$computer->getLastLogOff();

$computer->getLastLogon();

$computer->getLastLogonTimestamp();
```
