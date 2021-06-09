# Events

Adldap2 events provide a method of listening for certain LDAP actions
that are called and execute tasks for that specific event.

> **Note**: The Adldap2 event dispatcher was actually derived from the
> [Laravel Framework](https://github.com/laravel/framework) with
> Broadcasting & Queuing omitted to remove extra dependencies
> that would be required with implementing those features.
>
> If you've utilized Laravel's events before, this will feel very familiar.

## Registering Listeners

> **Note**: Before we get to registering listeners, it's crucial to know that events throughout
> Adldap2 are fired irrespective of the current connection or provider in use.
>
> This means that when using multiple LDAP connections, the same events will be fired.
> 
> This allows you to set listeners on events that occur for all LDAP connections you utilize.
>
> If you are required to determine which events are fired from alternate connections, see [below](#determining-the-connection).

To register a listener on an event, retrieve the event dispatcher and call the `listen()` method:

```php
use Adldap\Auth\Events\Binding;

$dispatcher = \Adldap\Adldap::getEventDispatcher();

$dispatcher->listen(Binding::class, function (Binding $event) {
    // Do something with the Binding event information:
    
    $event->connection; // Adldap\Connections\Ldap instance
    $event->username; // 'jdoe@acme.org'
    $event->password; // 'super-secret'
});
```

The first argument is the event name you would like to listen for, and the
second is either a closure or class name that should handle the event:

Using a class:

> **Note**: When using just a class name, the class must contain a public `handle()` method that will handle the event.

```php
use Adldap\Adldap;
use Adldap\Auth\Events\Binding;

$dispatcher = Adldap::getEventDispatcher();

$dispatcher->listen(Binding::class, MyApp\BindingEventHandler::class);
```

```php
namespace MyApp;

use Adldap\Auth\Events\Binding;

class BindingEventHandler
{
    public function handle(Binding $event)
    {
        // Handle the event...
    }
}
```

## Model Events

Model events are handled the same way as authentication events.

Simply call the event dispatcher `listen()` method with the model event you are wanting to listen for:

```php
use Adldap\Models\Events\Saving;

$dispatcher = \Adldap\Adldap::getEventDispatcher();

$dispatcher->listen(Saving::class, function (Saving $event) {
    // Do something with the Saving event information:
    
    // Returns the model instance being saved eg. `Adldap\Models\Entry`
    $event->getModel();
});
```

## Wildcard Event Listeners

You can register listeners using the `*` as a wildcard parameter to catch multiple events with the same listener.

Wildcard listeners will receive the event name as their first argument, and the entire event data array as their second argument:

```php
$dispatcher = Adldap::getEventDispatcher();

// Listen for all model events.
$dispatcher->listen('Adldap\Models\Events\*', function ($eventName, array $data) {
    echo $eventName; // Returns 'Adldap\Models\Events\Updating'
    var_dump($data); // Returns [0] => (object) Adldap\Models\Events\Updating;
});

$user = $provider->search()->users()->find('jdoe');

$user->setTelephoneNumber('555 555-5555');

$user->save();
```

## Determining the Connection

If you're using multiple LDAP connections and you require the ability to determine which events belong
to a certain connection, you can do so by verifying the host of the LDAP connection.

Here's an example:

```php
$dispatcher = Adldap::getEventDispatcher();

$dispatcher->listen(\Adldap\Models\Events\Creating::class, function ($event) {
    $connection = $event->model->getConnection();
    
    $host = $connection->getHost();
    
    echo $host; // Displays 'ldap://192.168.1.1:386'
});
```

Another example with auth events:

```php
$dispatcher = Adldap::getEventDispatcher();

$dispatcher->listen(\Adldap\Auth\Events\Binding::class, function ($event) {
    $connection = $event->connection;
    
    $host = $connection->getHost();
    
    echo $host; // Displays 'ldap://192.168.1.1:386'
});
```

## List of Events

### Authentication Events

There are several events that are fired during initial and subsequent binds to your configured LDAP server.

Here is a list of all events that are fired:

| Event| Description |
|---|---|
| Adldap\Auth\Events\Attempting | When any authentication attempt is called via: `$provider->auth()->attempt()` |
| Adldap\Auth\Events\Passed | When any authentication attempts pass via: `$provider->auth()->attempt()` |
| Adldap\Auth\Events\Failed | When any authentication attempts fail via: `$provider->auth()->attempt()` *Or* `$provider->auth()->bind()` |
| Adldap\Auth\Events\Binding | When any LDAP bind attempts occur via: `$provider->auth()->attempt()` *Or* `$provider->auth()->bind()` |
| Adldap\Auth\Events\Bound | When any LDAP bind attempts are successful via: `$provider->auth()->attempt()` *Or* `$provider->auth()->bind()` |

### Model Events

There are several events that are fired during the creation, updating and deleting of all models.

Here is a list of all events that are fired:

| Event | Description |
|---|---|
| Adldap\Models\Events\Saving | When a model is in the process of being saved via: `$model->save()` |
| Adldap\Models\Events\Saved | When a model has been successfully saved via: `$model->save()` |
| Adldap\Models\Events\Creating | When a model is being created via: `$model->save()` *Or* `$model->create()` |
| Adldap\Models\Events\Created | When a model has been successfully created via: `$model->save()` *Or* `$model->create()` |
| Adldap\Models\Events\Updating | When a model is being updated via: `$model->save()` *Or* `$model->update()` |
| Adldap\Models\Events\Updated | When a model has been successfully updated via: `$model->save()` *Or* `$model->update()` |
| Adldap\Models\Events\Deleting | When a model is being deleted via: `$model->delete()` |
| Adldap\Models\Events\Deleted | When a model has been successfully deleted via: `$model->delete()` |