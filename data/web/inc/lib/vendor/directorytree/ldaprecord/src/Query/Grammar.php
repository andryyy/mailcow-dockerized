<?php

namespace LdapRecord\Query;

use UnexpectedValueException;

class Grammar
{
    /**
     * The query operators and their method names.
     *
     * @var array
     */
    public $operators = [
        '*'               => 'has',
        '!*'              => 'notHas',
        '='               => 'equals',
        '!'               => 'doesNotEqual',
        '!='              => 'doesNotEqual',
        '>='              => 'greaterThanOrEquals',
        '<='              => 'lessThanOrEquals',
        '~='              => 'approximatelyEquals',
        'starts_with'     => 'startsWith',
        'not_starts_with' => 'notStartsWith',
        'ends_with'       => 'endsWith',
        'not_ends_with'   => 'notEndsWith',
        'contains'        => 'contains',
        'not_contains'    => 'notContains',
    ];

    /**
     * The query wrapper.
     *
     * @var string|null
     */
    protected $wrapper;

    /**
     * Get all the available operators.
     *
     * @return array
     */
    public function getOperators()
    {
        return array_keys($this->operators);
    }

    /**
     * Wraps a query string in brackets.
     *
     * Produces: (query)
     *
     * @param string $query
     * @param string $prefix
     * @param string $suffix
     *
     * @return string
     */
    public function wrap($query, $prefix = '(', $suffix = ')')
    {
        return $prefix.$query.$suffix;
    }

    /**
     * Compiles the Builder instance into an LDAP query string.
     *
     * @param Builder $query
     *
     * @return string
     */
    public function compile(Builder $query)
    {
        if ($this->queryMustBeWrapped($query)) {
            $this->wrapper = 'and';
        }

        $filter = $this->compileRaws($query)
            .$this->compileWheres($query)
            .$this->compileOrWheres($query);

        switch ($this->wrapper) {
            case 'and':
                return $this->compileAnd($filter);
            case 'or':
                return $this->compileOr($filter);
            default:
                return $filter;
        }
    }

    /**
     * Determine if the query must be wrapped in an encapsulating statement.
     *
     * @param Builder $query
     *
     * @return bool
     */
    protected function queryMustBeWrapped(Builder $query)
    {
        return ! $query->isNested() && $this->hasMultipleFilters($query);
    }

    /**
     * Assembles all of the "raw" filters on the query.
     *
     * @param Builder $builder
     *
     * @return string
     */
    protected function compileRaws(Builder $builder)
    {
        return $this->concatenate($builder->filters['raw']);
    }

    /**
     * Assembles all where clauses in the current wheres property.
     *
     * @param Builder $builder
     * @param string  $type
     *
     * @return string
     */
    protected function compileWheres(Builder $builder, $type = 'and')
    {
        $filter = '';

        foreach ($builder->filters[$type] as $where) {
            $filter .= $this->compileWhere($where);
        }

        return $filter;
    }

    /**
     * Assembles all or where clauses in the current orWheres property.
     *
     * @param Builder $query
     *
     * @return string
     */
    protected function compileOrWheres(Builder $query)
    {
        $filter = $this->compileWheres($query, 'or');

        if ($this->hasMultipleFilters($query)) {
            // Here we will detect whether the entire query can be
            // wrapped inside of an "or" statement by checking
            // how many filter statements exist for each type.
            if ($this->queryCanBeWrappedInSingleOrStatement($query)) {
                $this->wrapper = 'or';
            } else {
                $filter = $this->compileOr($filter);
            }
        }

        return $filter;
    }

    /**
     * Determine if the query can be wrapped in a single or statement.
     *
     * @param Builder $query
     *
     * @return bool
     */
    protected function queryCanBeWrappedInSingleOrStatement(Builder $query)
    {
        return $this->has($query, 'or', '>=', 1) &&
            $this->has($query, 'and', '<=', 1) &&
            $this->has($query, 'raw', '=', 0);
    }

    /**
     * Concatenates filters into a single string.
     *
     * @param array $bindings
     *
     * @return string
     */
    public function concatenate(array $bindings = [])
    {
        // Filter out empty query segments.
        return implode(
            array_filter($bindings, [$this, 'bindingValueIsNotEmpty'])
        );
    }

    /**
     * Determine if the binding value is not empty.
     *
     * @param string $value
     *
     * @return bool
     */
    protected function bindingValueIsNotEmpty($value)
    {
        return ! empty($value);
    }

    /**
     * Determine if the query is using multiple filters.
     *
     * @param Builder $query
     *
     * @return bool
     */
    protected function hasMultipleFilters(Builder $query)
    {
        return $this->has($query, ['and', 'or', 'raw'], '>', 1);
    }

    /**
     * Determine if the query contains the given filter statement type.
     *
     * @param Builder      $query
     * @param string|array $type
     * @param string       $operator
     * @param int          $count
     *
     * @return bool
     */
    protected function has(Builder $query, $type, $operator = '>=', $count = 1)
    {
        $types = (array) $type;

        $filters = 0;

        foreach ($types as $type) {
            $filters += count($query->filters[$type]);
        }

        switch ($operator) {
            case '>':
                return $filters > $count;
            case '>=':
                return $filters >= $count;
            case '<':
                return $filters < $count;
            case '<=':
                return $filters <= $count;
            default:
                return $filters == $count;
        }
    }

    /**
     * Returns a query string for equals.
     *
     * Produces: (field=value)
     *
     * @param string $field
     * @param string $value
     *
     * @return string
     */
    public function compileEquals($field, $value)
    {
        return $this->wrap($field.'='.$value);
    }

    /**
     * Returns a query string for does not equal.
     *
     * Produces: (!(field=value))
     *
     * @param string $field
     * @param string $value
     *
     * @return string
     */
    public function compileDoesNotEqual($field, $value)
    {
        return $this->compileNot($this->compileEquals($field, $value));
    }

    /**
     * Alias for does not equal operator (!=) operator.
     *
     * Produces: (!(field=value))
     *
     * @param string $field
     * @param string $value
     *
     * @return string
     */
    public function compileDoesNotEqualAlias($field, $value)
    {
        return $this->compileDoesNotEqual($field, $value);
    }

    /**
     * Returns a query string for greater than or equals.
     *
     * Produces: (field>=value)
     *
     * @param string $field
     * @param string $value
     *
     * @return string
     */
    public function compileGreaterThanOrEquals($field, $value)
    {
        return $this->wrap("$field>=$value");
    }

    /**
     * Returns a query string for less than or equals.
     *
     * Produces: (field<=value)
     *
     * @param string $field
     * @param string $value
     *
     * @return string
     */
    public function compileLessThanOrEquals($field, $value)
    {
        return $this->wrap("$field<=$value");
    }

    /**
     * Returns a query string for approximately equals.
     *
     * Produces: (field~=value)
     *
     * @param string $field
     * @param string $value
     *
     * @return string
     */
    public function compileApproximatelyEquals($field, $value)
    {
        return $this->wrap("$field~=$value");
    }

    /**
     * Returns a query string for starts with.
     *
     * Produces: (field=value*)
     *
     * @param string $field
     * @param string $value
     *
     * @return string
     */
    public function compileStartsWith($field, $value)
    {
        return $this->wrap("$field=$value*");
    }

    /**
     * Returns a query string for does not start with.
     *
     * Produces: (!(field=*value))
     *
     * @param string $field
     * @param string $value
     *
     * @return string
     */
    public function compileNotStartsWith($field, $value)
    {
        return $this->compileNot($this->compileStartsWith($field, $value));
    }

    /**
     * Returns a query string for ends with.
     *
     * Produces: (field=*value)
     *
     * @param string $field
     * @param string $value
     *
     * @return string
     */
    public function compileEndsWith($field, $value)
    {
        return $this->wrap("$field=*$value");
    }

    /**
     * Returns a query string for does not end with.
     *
     * Produces: (!(field=value*))
     *
     * @param string $field
     * @param string $value
     *
     * @return string
     */
    public function compileNotEndsWith($field, $value)
    {
        return $this->compileNot($this->compileEndsWith($field, $value));
    }

    /**
     * Returns a query string for contains.
     *
     * Produces: (field=*value*)
     *
     * @param string $field
     * @param string $value
     *
     * @return string
     */
    public function compileContains($field, $value)
    {
        return $this->wrap("$field=*$value*");
    }

    /**
     * Returns a query string for does not contain.
     *
     * Produces: (!(field=*value*))
     *
     * @param string $field
     * @param string $value
     *
     * @return string
     */
    public function compileNotContains($field, $value)
    {
        return $this->compileNot($this->compileContains($field, $value));
    }

    /**
     * Returns a query string for a where has.
     *
     * Produces: (field=*)
     *
     * @param string $field
     *
     * @return string
     */
    public function compileHas($field)
    {
        return $this->wrap("$field=*");
    }

    /**
     * Returns a query string for a where does not have.
     *
     * Produces: (!(field=*))
     *
     * @param string $field
     *
     * @return string
     */
    public function compileNotHas($field)
    {
        return $this->compileNot($this->compileHas($field));
    }

    /**
     * Wraps the inserted query inside an AND operator.
     *
     * Produces: (&query)
     *
     * @param string $query
     *
     * @return string
     */
    public function compileAnd($query)
    {
        return $query ? $this->wrap($query, '(&') : '';
    }

    /**
     * Wraps the inserted query inside an OR operator.
     *
     * Produces: (|query)
     *
     * @param string $query
     *
     * @return string
     */
    public function compileOr($query)
    {
        return $query ? $this->wrap($query, '(|') : '';
    }

    /**
     * Wraps the inserted query inside an NOT operator.
     *
     * @param string $query
     *
     * @return string
     */
    public function compileNot($query)
    {
        return $query ? $this->wrap($query, '(!') : '';
    }

    /**
     * Assembles a single where query.
     *
     * @param array $where
     *
     * @return string
     *
     * @throws UnexpectedValueException
     */
    protected function compileWhere(array $where)
    {
        if (! array_key_exists($where['operator'], $this->operators)) {
            throw new UnexpectedValueException('Invalid LDAP filter operator ['.$where['operator'].']');
        }

        $method = 'compile'.ucfirst($this->operators[$where['operator']]);

        return $this->{$method}($where['field'], $where['value']);
    }
}
