<?php

namespace Adldap\Models\Events;

use Adldap\Models\Model;

abstract class Event
{
    /**
     * The model that the event is being triggered on.
     *
     * @var Model
     */
    protected $model;

    /**
     * Constructor.
     *
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Returns the model that generated the event.
     *
     * @return Model
     */
    public function getModel()
    {
        return $this->model;
    }
}
