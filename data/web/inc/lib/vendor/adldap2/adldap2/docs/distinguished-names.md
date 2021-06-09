## Working With Distinguished Names

Working with DN strings are a pain, but they're about to get easier. Adldap includes a DN builder for easily modifying and
creating DN strings.

> **Note**: All values inserted into DN methods are escaped. You do not need to escape **any** values before hand.

#### Creating a New DN

To create a new DN, construct a new `Adldap\Models\Attributes\DistinguishedName` instance:

```php
$dn = new Adldap\Models\Attributes\DistinguishedName();
```
    
You can also pass in a current DN string and start modifying it:

```php
$currentDn = 'cn=John Doe,ou=Accounting,dc=corp,dc=acme,dc=org';

$dn = new Adldap\Models\Attributes\DistinguishedName($currentDn);
```
    
#### Adding / Removing a Domain Component

```php
// Add Domain Component
$dn->addDc('corp');

// Remove Domain Component
$dn->removeDc('corp');
```

#### Adding / Removing an Organizational Unit

```php
// Add Organizational Unit
$dn->addOu('Accounting');
    
// Remove Organizational Unit
$dn->removeOu('Accounting');
```

#### Adding / Removing Common Names

```php
// Add Common Name
$dn->addCn('John Doe');
    
// Remove Common Name
$dn->removeCn('John Doe');   
```

#### Setting a base

If you'd like to set the base DN, such as a domain component RDN, use the `setBase()` method:

```php
$base = 'dc=corp,dc=acme,dc=org';

$dn->setBase($base);
```

#### Creating a DN From A Model

When you're creating a new LDAP record, you'll need to create a distinguished name as well. Let's go through an example of
creating a new user.

```php
$user = $provider->make()->user();

$user->setCommonName('John Doe');
$user->setFirstName('John');
$user->setLastName('Doe');
```

So we've set the basic information on the user, but we run into trouble when we want to put the user into a certain container
(such as 'Accounting') which is done through the DN. Let's go through this example:

```php
$dn = $user->getDnBuilder();

$dn->addCn($user->getCommonName());
$dn->addOu('Accounting');
$dn->addDc('corp');
$dn->addDc('acme');
$dn->addDc('org');

// Returns 'cn=John Doe,ou=Accounting,dc=corp,dc=acme,dc=org'
echo $dn->get();

// The DistinguishedName object also contains the __toString() magic method
// so you can also just echo the object itself
echo $dn;
```
    
Now we've built a DN, and all we have to do is set it on the new user:    

```php
$user->setDn($dn);

$user->save();
```

#### Modifying a DN From A Model

When you've received a model from a search result, you can build and modify the models DN like so:

```php
$user = $ad->users()->find('jdoe');

$dn = $user->getDnBuilder();

$dn->addOu('Users');

$user->setDn($dn)->save();
```

#### Retrieving the RDN components

To retrieve all of the RDN components of a Distinguished Name, call `getComponents()`:

```php
$dn = new Adldap\Models\Attributes\DistinguishedName(
    'cn=John Doe,ou=Accounting,dc=corp,dc=acme,dc=org'
);

$components = $dn->getComponents();

var_dump($components);

// Output:
// array:5 [▼
//   "cn" => array:1 [▼
//     0 => "John Doe"
//   ]
//   "uid" => []
//   "ou" => array:1 [▼
//     0 => "Accounting"
//   ]
//   "dc" => array:3 [▼
//     0 => "corp"
//     1 => "acme"
//     2 => "org"
//   ]
//   "o" => []
// ]
```

You can also specify a component you would like returned by supplying it as an argument:

```php
$dn = new Adldap\Models\Attributes\DistinguishedName(
    'cn=John Doe,ou=Accounting,dc=corp,dc=acme,dc=org'
);

$dcs = $dn->getComponents('dc');

var_dump($dcs);

// Output:
// array:3 [▼
//   0 => "corp"
//   1 => "acme"
//   2 => "org"
// ]
```
