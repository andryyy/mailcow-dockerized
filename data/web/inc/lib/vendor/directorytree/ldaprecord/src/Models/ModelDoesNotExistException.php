<?php

namespace LdapRecord\Models;

use LdapRecord\LdapRecordException;

class ModelDoesNotExistException extends LdapRecordException
{
    /**
     * The class name of the model that does not exist.
     *
     * @var Model
     */
    protected $model;

    /**
     * Create a new exception for the given model.
     *
     * @param Model $model
     *
     * @return ModelDoesNotExistException
     */
    public static function forModel(Model $model)
    {
        return (new static())->setModel($model);
    }

    /**
     * Set the model that does not exist.
     *
     * @param Model $model
     *
     * @return ModelDoesNotExistException
     */
    public function setModel(Model $model)
    {
        $this->model = $model;

        $class = get_class($model);

        $this->message = "Model [{$class}] does not exist.";

        return $this;
    }
}
