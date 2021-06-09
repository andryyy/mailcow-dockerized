# Creating / Updating

## Introduction

Adldap2 implements the [ActiveRecord](https://en.wikipedia.org/wiki/Active_record_pattern) pattern.
This means that each LDAP record in your directory is represented as it's own model instance.

## Creating

Creating LDAP entries manually is always a pain, but Adldap2 makes it effortless. Let's get started.

When you have a provider instance, call the `make()` method. This returns an `Adldap\Models\Factory` instance:

```php
$factory = $provider->make();
```

Or you can chain all methods if you'd prefer:

```php
$user = $provider->make()->user();
```

### Available Make Methods

When calling a make method, all of them accept an `$attributes` parameter
to fill the model with your specified attributes.

```php
// Adldap\Models\User
$user = $provider->make()->user([
    'cn' => 'John Doe',
]);

// Adldap\Models\Computer
$computer = $provider->make()->computer([
    'cn' => 'COMP-101',
]);

// Adldap\Models\Contact
$contact = $provider->make()->contact([
    'cn' => 'Suzy Doe',
]);

// Adldap\Models\Container
$container = $provider->make()->container([
    'cn' => 'VPN Users',
]);

// Adldap\Models\Group
$group = $provider->make()->group([
    'cn' => 'Managers',
]);

// Adldap\Models\OrganizationalUnit
$ou = $provider->make()->ou([
    'name' => 'Acme',
]);
```

## Saving

When you have any model instance, you can call the `save()` method to persist the
changes to your server. This method returns a `boolean`. For example:

```php
$user = $provider->make()->user([
    'cn' => 'New User',
]);

if ($user->save()) {
    // User was saved.
} else {
    // There was an issue saving this user.
}
```

> **Note**: When a model is saved successfully (whether created or updated), the
> models attributes are re-synced in the background from your LDAP server.
> 
> This allows you to perform other operations during the same
> request that require an existing model.

### Creating (Manually)

If you are sure the model **does not exist** already inside your LDAP directory, you can use the `create()` method:

```php
$user = $provider->make()->user([
    'cn' => 'New User',
]);

if ($user->create()) {
    // User was created.
} else {
    // There was an issue creating this user.
}
```

> **Note**: When you call the create method, if the model does not have a
> distinguished name, one will automatically be generated for you using your
> `base_dn` set in your configuration and the models common name.

### Updating (Manually)

If you are sure the model **does exist** already inside your LDAP directory, you can use the `update()` method:

```php
$user = $provider->search()->whereEquals('cn', 'John Doe')->firstOrFail();

$user->displayName = 'Suzy Doe';

if ($user->update()) {
    // User was updated.
} else {
    // There was an issue updating this user.
}
```

## Checking Existence

If you need to check the existence of a model, use the property `exists`.

How does it know if the model exists in your LDAP directory? Well, when models are constructed from
search results, the `exists` property on the model is set to `true`.

```php
$user = $provider->search()->find('jdoe');

$user->exists; // Returns true.

if ($user->delete()) {
    $user->exists; // Returns false.
}
```

If a model is created successfully, the `exists` property is set to `true`:

```php
$user = $provider->make()->user([
    'cn' => 'John Doe',
]);

$user->exists; // Returns false.

if ($user->save()) {
    $user->exists; // Returns true.
}
```

## Attributes

Due to LDAPs multi-valued nature, all LDAP attributes inside a model have their own array.

For example, a models attributes may contain the following:

```php
var_dump($user->getAttributes());

// Returns:
/*
[
    'cn' => [
        0 => 'John Doe',
    ],
    'sn' => [
        0 => 'Doe',
    ],
    'givenname' => [
        0 => 'John'
    ],
    'useraccountcontrol' => [
        0 => 512
    ],
    'mail' => [
        0 => 'jdoe@acme.org',
        1 => 'john-doe@acme.org',
    ],
    'memberof' => [
        0 => 'cn=Accountants,ou=Groups,dc=acme,dc=org',
        1 => 'cn=Employees,ou=Groups,dc=acme,dc=org',
        2 => 'cn=Users,ou=Groups,dc=acme,dc=org',
    ],
]
*/
```

You can notice in the above dumped array that each attribute contains
its own array with a value assigned to the first key.

Since all models extend from the base class `Adldap\Models\Model`, there
are many useful methods that you can use on every model to easily
retrieve these attributes you're looking for.

### Getting Attributes

You can get attributes in a few ways:

```php
// Returns an array all of the users attributes.
$user->getAttributes();

// Returns an array of all the users email addresses.
// Returns `null` if non-existent.
$user->getAttribute('mail');

// Returns the users first email address.
// Returns `null` if non-existent.
$user->getAttribute('mail', 0);

// Returns the users first email address.
// Returns `null` if non-existent.
$user->getFirstAttribute('mail');

// Returns an array of all the users email addresses. 
$user->mail;

// Returns the users first email address.
$user->mail[0];
```

#### Using a Getter

Some attributes have methods for easier retrieval so you don't need to look up the LDAP attribute name.

For example, to retrieve a users email address, use the method `getEmail()`:

```php
$user->getEmail();
```

##### Other Methods

The following methods are available on all returned models:

```php
// Returns the model's 'name' attribute.
$model->getName();

// Returns the model's 'cn' attribute.
$model->getCommonName();

// Returns the model's 'displayname' attribute.
$model->getDisplayName();

// Returns the model's 'samaccountname' attriubte.
$model->getAccountName();

// Returns the model's 'samaccounttype` attribute.
$model->getAccountType();

// Returns the model's 'whencreated` attribute.
$model->getCreatedAt();

// Returns the model's 'whencreated` attribute in a MySQL timestamp format.
$model->getCreatedAtDate();

// Returns the model's 'whencreated' attribute in unix time.
$model->getCreatedAtTimestamp();

// Returns the model's 'whenchanged` attribute.
$model->getUpdatedAt();

// Returns the model's 'whenchanged` attribute in a MySQL timestamp format.
$model->getUpdatedAtDate();

// Returns the model's 'whenchanged` attribute in unix time.
$model->getUpdatedAtTimestamp();

// Returns the model's 'objectclass' attribute.
$model->getObjectClass();

// Returns the model's root object category string.
$model->getObjectCategory();

// Returns the model's object category in an array.
$model->getObjectCategoryArray();

// Returns the model's object category distinguished name.
$model->getObjectCategoryDn();

// Returns the model's SID in binary.
$model->getObjectSid();

// Returns the model's GUID in binary.
$model->getObjectGuid();

// Returns the model's SID in a string.
$model->getConvertedSid();

// Returns the model's GUID in a string.
$model->getConvertedGuid();

// Returns the model's primary group ID.
$model->getPrimaryGroupId();

// Returns the model's 'instancetype' attribute.
$model->getInstanceType();

// Returns the model's 'maxpwdage' attribute.
$model->getMaxPasswordAge();
```

For more documentation on specific getters, please take a look at the relevant model documentation.

#### Getting Dirty (Modified) Attributes

You can get a models modified attributes using the `getDirty()` method:

```php
$user = $provider->search()->users()->find('john');

// Returns array [0 => 'John Doe']
var_dump($user->cn);

$user->setAttribute('cn', 'Jane Doe');

// Returns array ['cn' => [0 => 'Jane Doe']]
var_dump($user->getDirty());

// The attribute has been modified - returns array [0 => 'Jane Doe']
var_dump($user->cn);
```

The method returns an array with the key being the modified attribute,
and the array being the new values of the attribute.

#### Getting Original (Unmodified) Attributes

You can get a models original attributes using the `getOriginal()` method:

```php
$user = $provider->search()->users()->find('john');

// Returns array [0 => 'John Doe']
var_dump($user->cn);

$user->setAttribute('cn', 'Jane Doe');

// The attribute has been modified - returns array [0 => 'Jane Doe']
var_dump($user->cn);

// Retrieving the original value - returns array [0 => 'John Doe']
var_dump($user->getOriginal()['cn']);
```

> **Note**: Keep in mind, when you `save()` a model, the models original
> attributes will be re-synchronized to the models new attributes.

### Setting Attributes

Just like getting model attributes, there's multiple ways of setting attributes as well:

```php
// Setting via method:
$user->setAttribute('cn', 'John Doe');

// Specifying a subkey for overwriting specific attributes:
$user->setAttribute('mail', 'other-mail@mail.com', 0);

// Setting the first attribute:
$user->setFirstAttribute('mail', 'jdoe@mail.com');

// Setting via property:
$user->cn = 'John Doe';

// Mass setting attributes:
$user->fill([
    'cn' => 'John Doe',
    'mail' => 'jdoe@mail.com',
]);
```

#### Setting Boolean Attributes

When setting boolean attribute values, you cannot use `0` / `1` / `true` / `false` as these
are simply converted to integer values when saving and your LDAP server will
likely return an error for doing so on certain attributes.

You will need to use the string versions of the boolean (`'TRUE'` / `'FALSE'`) for the
boolean attribute to be set properly on your LDAP server.

Here's an example:

```php
$user->setFirstAttribute('msExchHideFromAddressLists', 'TRUE');

$user->save();
```

### Creating Attributes

To create an attribute that does not exist on the model, you can set it like a regular property:

```php
$user = $provider->search()->whereEquals('cn', 'John Doe')->firstOrFail();

$user->new = 'New Attribute';

$user->save();
```

If the set attribute does not exist on the model already,
it will automatically be created when you call the `save()` method.

If you'd like manually create new attributes individually, call the `createAttribute($attribute, $value)` method:

```php
if ($user->createAttribute('new', 'New Attribute')) {
    // Attribute created.
}
```

### Updating Attributes

To modify an attribute you can either use a setter method, or by setting it manually:

> **Note**: You can also utilize setters to create new attributes if your model does not already have the attribute.

```php
$user = $provider->search()->whereEquals('cn', 'John Doe')->firstOrFail();

$user->cn = 'New Name';

// Or use a setter:

$user->setCommonName('New Name');

$user->save();
```

If you'd like to update attributes individually, call the `updateAttribute($attribute, $value)` method:

```php
if ($user->updateAttribute('cn', 'New Name')) {
    // Successfully updated attribute.
}
```

### Removing Attributes

To remove attributes, set the attribute to `NULL`:

```php
$user->cn = null;

$user->save();
```

Or, you can call the `deleteAttribute($attribute)` method:

```php
if ($user->deleteAttribute('cn')) {
    // Attribute has been deleted.
}
```

### Checking Attributes

#### Checking Existence of Attributes

To see if a model contains an attribute, use the method `hasAttribute()`:

```php
// Checking if a base attribute exists:
if ($user->hasAttribute('mail')) {
    // This user contains an email address.
}

// Checking if a sub attribute exists, by key:
if ($user->hasAttribute('mail', 1)) {
    // This user contains a second email address.
}
```

#### Counting the Models Attributes

To retrieve the total number of attributes, use the method `countAttributes()`:

```php
$count = $user->countAttributes();

var_dump($count); // Returns int
```

#### Checking if a Model is contained in an OU

To check if a model is located inside an OU, use the `inOu()` method:

```php
if ($model->inOu('User Accounts')) {
    // This model is inside the 'User Accounts' OU.
}
```

You can also use an OU model instance:

```php
$serviceAccounts = $provider->search()->ous()->find('Service Accounts');

if ($model->inOu($serviceAccounts)) {
    // This model is inside the 'Service Accounts' OU.
}
```

#### Checking if a Model is Writable

To check if the model can be written to, use the method `isWritable()`:

```php
if ($model->isWritable()) {
    // You can modify this model.
}
```

### Force Re-Syncing A Models Attributes

If you need to forcefully re-sync a models attributes, use the method `syncRaw()`:

```php
$user->syncRaw();
```

> **Note**: This will query your LDAP server for the current model, and re-synchronize
> it's attributes. This is only recommended if your creating / updating / deleting
> attributes manually through your LDAP connection.

## Moving / Renaming

To move a user from one DN or OU to another, use the `move()` method:

> **Note**: The `move()` method is actually an alias for the `rename()` method.

```php
// New parent distiguished name.
$newParentDn = 'OU=New Ou,DC=corp,DC=local';

if ($user->move($newParentDn)) {
    // User was successfully moved to the new OU.
}
```

You can also provide a model to move the child model into:

```php
// New parent OU.
$newParentOu = $provider->search()->ous()->find('Accounting');

if ($user->move($newParentOu)) {
    // User was successfully moved to the new OU.
}
```

If you would like to keep the models old RDN along side their new RDN, pass in false in the second parameter:

```php
// New parent distiguished name.
$newParentDn = 'OU=New Ou,DC=corp,DC=local';

if ($user->move($newParentDn, $deleteOldRdn = false)) {
    // User was successfully moved to the new OU,
    // and their old RDN has been left in-tact.
}
```

To rename a users DN, just pass in their new relative distinguished name in the `rename()` method:

```php
$newRdn = 'cn=New Name';

if ($user->rename($newRdn)) {
    // User was successfully renamed.
}
```

## Deleting

To delete a model, just call the `delete()` method:

```php
$user = $provider->search()->whereEquals('cn', 'John Doe')->firstOrFail();

echo $user->exists; // Returns true.

if ($user->delete()) {
    // Successfully deleted user.

    echo $user->exists; // Returns false.
}
```

## Extending

> **Note**: This feature was introduced in `v8.0.0`.

To use your own models, you will need to create a new [Schema](../schema.md).

Once you have created your own schema, you must insert it inside the construct of your provider.

Let's walk through this process.

First we'll create our model we'd like to extend / override:

> **Note**: Your custom model **must** extend from an existing Adldap2 model.
> This is due to methods and attributes that only exist on these classes.

```php
namespace App\Ldap\Models;

use Adldap\Models\User as Model;

class User extends Model
{
    public function getCommonName()
    {
        // Overriding model method.
    }
}
```

Now, we'll create our custom schema and return our models class name:

```php
namespace App\Ldap\Schemas;

use App\Ldap\Models\User;

class LdapSchema extends ActiveDirectory
{
    public function userModel()
    {
        return User::class;
    }
}
```

Finally, when we create a provider, we need to insert our Schema into the configuration:

```php
$config = [
    'hosts' => ['...'],
    
    'username' => 'admin',
    'password' => 'P@ssword',
    
    'schema' => MyApp\LdapSchema::class,
];

$ad = new Adldap($config);

$provider = $ad->connect();

// If `jdoe` exists, your custom model will be returned.
$user = $provider->search()->users()->find('jdoe');
```
