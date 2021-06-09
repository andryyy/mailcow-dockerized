<?php

namespace LdapRecord\Models\Concerns;

use Closure;
use InvalidArgumentException;
use LdapRecord\Models\Scope;

trait HasGlobalScopes
{
    /**
     * Register a new global scope on the model.
     *
     * @param Scope|Closure|string $scope
     * @param Closure|null         $implementation
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     */
    public static function addGlobalScope($scope, Closure $implementation = null)
    {
        if (is_string($scope) && ! is_null($implementation)) {
            return static::$globalScopes[static::class][$scope] = $implementation;
        } elseif ($scope instanceof Closure) {
            return static::$globalScopes[static::class][spl_object_hash($scope)] = $scope;
        } elseif ($scope instanceof Scope) {
            return static::$globalScopes[static::class][get_class($scope)] = $scope;
        }

        throw new InvalidArgumentException('Global scope must be an instance of Closure or Scope.');
    }

    /**
     * Determine if a model has a global scope.
     *
     * @param Scope|string $scope
     *
     * @return bool
     */
    public static function hasGlobalScope($scope)
    {
        return ! is_null(static::getGlobalScope($scope));
    }

    /**
     * Get a global scope registered with the model.
     *
     * @param Scope|string $scope
     *
     * @return Scope|Closure|null
     */
    public static function getGlobalScope($scope)
    {
        if (array_key_exists(static::class, static::$globalScopes)) {
            $scopeName = is_string($scope) ? $scope : get_class($scope);

            return array_key_exists($scopeName, static::$globalScopes[static::class])
                ? static::$globalScopes[static::class][$scopeName]
                : null;
        }
    }

    /**
     * Get the global scopes for this class instance.
     *
     * @return array
     */
    public function getGlobalScopes()
    {
        return array_key_exists(static::class, static::$globalScopes)
            ? static::$globalScopes[static::class]
            : [];
    }
}
