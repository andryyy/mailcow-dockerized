<?php

namespace Adldap\Models\Concerns;

use Adldap\Adldap;
use Adldap\Models\Events\Event;

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
        return Adldap::getEventDispatcher()->fire($event);
    }
}
