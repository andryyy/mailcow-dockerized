<?php

namespace Adldap\Events;

trait DispatchesEvents
{
    /**
     * The event dispatcher instance.
     *
     * @var DispatcherInterface
     */
    protected static $dispatcher;

    /**
     * Get the event dispatcher instance.
     *
     * @return DispatcherInterface
     */
    public static function getEventDispatcher()
    {
        // If no event dispatcher has been set, well instantiate and
        // set one here. This will be our singleton instance.
        if (!isset(static::$dispatcher)) {
            static::setEventDispatcher(new Dispatcher());
        }

        return static::$dispatcher;
    }

    /**
     * Set the event dispatcher instance.
     *
     * @param DispatcherInterface $dispatcher
     *
     * @return void
     */
    public static function setEventDispatcher(DispatcherInterface $dispatcher)
    {
        static::$dispatcher = $dispatcher;
    }

    /**
     * Unset the event dispatcher instance.
     *
     * @return void
     */
    public static function unsetEventDispatcher()
    {
        static::$dispatcher = null;
    }
}
