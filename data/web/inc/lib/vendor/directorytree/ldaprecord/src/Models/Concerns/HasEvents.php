<?php

namespace LdapRecord\Models\Concerns;

use Closure;
use LdapRecord\Models\Events\Event;

trait HasEvents
{
    /**
     * Fires the specified model event.
     *
     * @param Event $event
     *
     * @return mixed
     */
    protected function fireModelEvent(Event $event)
    {
        return static::getConnectionContainer()->getEventDispatcher()->fire($event);
    }

    /**
     * Listens to a model event.
     *
     * @param string  $event
     * @param Closure $listener
     *
     * @return mixed
     */
    protected function listenForModelEvent($event, Closure $listener)
    {
        return static::getConnectionContainer()->getEventDispatcher()->listen($event, $listener);
    }
}
