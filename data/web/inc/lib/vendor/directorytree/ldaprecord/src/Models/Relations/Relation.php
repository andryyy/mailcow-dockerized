<?php

namespace LdapRecord\Models\Relations;

use LdapRecord\Models\Entry;
use LdapRecord\Models\Model;
use LdapRecord\Query\Collection;
use LdapRecord\Query\Model\Builder;

/**
 * @method bool exists($models = null) Determine if the relation contains all of the given models, or any models
 * @method bool contains($models)      Determine if any of the given models are contained in the relation
 */
abstract class Relation
{
    /**
     * The underlying LDAP query.
     *
     * @var Builder
     */
    protected $query;

    /**
     * The parent model instance.
     *
     * @var Model
     */
    protected $parent;

    /**
     * The related models.
     *
     * @var array
     */
    protected $related;

    /**
     * The relation key.
     *
     * @var string
     */
    protected $relationKey;

    /**
     * The foreign key.
     *
     * @var string
     */
    protected $foreignKey;

    /**
     * The default relation model.
     *
     * @var string
     */
    protected $default = Entry::class;

    /**
     * Constructor.
     *
     * @param Builder $query
     * @param Model   $parent
     * @param mixed   $related
     * @param string  $relationKey
     * @param string  $foreignKey
     */
    public function __construct(Builder $query, Model $parent, $related, $relationKey, $foreignKey)
    {
        $this->query = $query;
        $this->parent = $parent;
        $this->related = (array) $related;
        $this->relationKey = $relationKey;
        $this->foreignKey = $foreignKey;

        $this->initRelation();
    }

    /**
     * Handle dynamic method calls to the relationship.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (in_array($method, ['exists', 'contains'])) {
            return $this->get('objectclass')->$method(...$parameters);
        }

        $result = $this->query->$method(...$parameters);

        if ($result === $this->query) {
            return $this;
        }

        return $result;
    }

    /**
     * Get the results of the relationship.
     *
     * @return Collection
     */
    abstract public function getResults();

    /**
     * Execute the relationship query.
     *
     * @param array|string $columns
     *
     * @return Collection
     */
    public function get($columns = ['*'])
    {
        return $this->getResultsWithColumns($columns);
    }

    /**
     * Get the results of the relationship while selecting the given columns.
     *
     * If the query columns are empty, the given columns are applied.
     *
     * @param array $columns
     *
     * @return Collection
     */
    protected function getResultsWithColumns($columns)
    {
        if (is_null($this->query->columns)) {
            $this->query->select($columns);
        }

        return $this->getResults();
    }

    /**
     * Get the first result of the relationship.
     *
     * @param array|string $columns
     *
     * @return Model|null
     */
    public function first($columns = ['*'])
    {
        return $this->get($columns)->first();
    }

    /**
     * Prepare the relation query.
     *
     * @return static
     */
    public function initRelation()
    {
        $this->query
            ->clearFilters()
            ->withoutGlobalScopes()
            ->setModel($this->getNewDefaultModel());

        return $this;
    }

    /**
     * Get the underlying query for the relation.
     *
     * @return Builder
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Get the parent model of the relation.
     *
     * @return Model
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Get the relation attribute key.
     *
     * @return string
     */
    public function getRelationKey()
    {
        return $this->relationKey;
    }

    /**
     * Get the related model classes for the relation.
     *
     * @return array
     */
    public function getRelated()
    {
        return $this->related;
    }

    /**
     * Get the relation foreign attribute key.
     *
     * @return string
     */
    public function getForeignKey()
    {
        return $this->foreignKey;
    }

    /**
     * Get the class name of the default model.
     *
     * @return string
     */
    public function getDefaultModel()
    {
        return $this->default;
    }

    /**
     * Get a new instance of the default model on the relation.
     *
     * @return Model
     */
    public function getNewDefaultModel()
    {
        $model = new $this->default();

        $model->setConnection($this->parent->getConnectionName());

        return $model;
    }

    /**
     * Get the foreign model by the given value.
     *
     * @param string $value
     *
     * @return Model|null
     */
    protected function getForeignModelByValue($value)
    {
        return $this->foreignKeyIsDistinguishedName()
            ? $this->query->find($value)
            : $this->query->findBy($this->foreignKey, $value);
    }

    /**
     * Returns the escaped foreign key value for use in an LDAP filter from the model.
     *
     * @param Model $model
     *
     * @return string
     */
    protected function getEscapedForeignValueFromModel(Model $model)
    {
        return $this->query->escape(
            $this->getForeignValueFromModel($model)
        )->both();
    }

    /**
     * Get the relation parents foreign value.
     *
     * @return string
     */
    protected function getParentForeignValue()
    {
        return $this->getForeignValueFromModel($this->parent);
    }

    /**
     * Get the foreign key value from the model.
     *
     * @param Model $model
     *
     * @return string
     */
    protected function getForeignValueFromModel(Model $model)
    {
        return $this->foreignKeyIsDistinguishedName()
                ? $model->getDn()
                : $this->getFirstAttributeValue($model, $this->foreignKey);
    }

    /**
     * Get the first attribute value from the model.
     *
     * @param Model  $model
     * @param string $attribute
     *
     * @return string|null
     */
    protected function getFirstAttributeValue(Model $model, $attribute)
    {
        return $model->getFirstAttribute($attribute);
    }

    /**
     * Transforms the results by converting the models into their related.
     *
     * @param Collection $results
     *
     * @return Collection
     */
    protected function transformResults(Collection $results)
    {
        $related = [];

        foreach ($this->related as $relation) {
            $related[$relation] = $relation::$objectClasses;
        }

        return $results->transform(function (Model $entry) use ($related) {
            $model = $this->determineModelFromRelated($entry, $related);

            return class_exists($model) ? $entry->convert(new $model()) : $entry;
        });
    }

    /**
     * Determines if the foreign key is a distinguished name.
     *
     * @return bool
     */
    protected function foreignKeyIsDistinguishedName()
    {
        return in_array($this->foreignKey, ['dn', 'distinguishedname']);
    }

    /**
     * Determines the model from the given relations.
     *
     * @param Model $model
     * @param array $related
     *
     * @return string|bool
     */
    protected function determineModelFromRelated(Model $model, array $related)
    {
        $objectClasses = $model->getAttribute('objectclass') ?? [];

        // We must normalize all the related models object class
        // names to the same case so we are able to properly
        // determine the owning model from search results.
        return array_search(
            $this->normalizeObjectClasses($objectClasses),
            array_map([$this, 'normalizeObjectClasses'], $related)
        );
    }

    /**
     * Sort and normalize the object classes.
     *
     * @param array $classes
     *
     * @return array
     */
    protected function normalizeObjectClasses($classes)
    {
        sort($classes);

        return array_map('strtolower', $classes);
    }
}
