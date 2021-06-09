<?php

namespace Adldap\Query;

class Grammar
{
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
     * @param Builder $builder
     *
     * @return string
     */
    public function compile(Builder $builder)
    {
        $ands = $builder->filters['and'];
        $ors = $builder->filters['or'];
        $raws = $builder->filters['raw'];

        $query = $this->concatenate($raws);

        $query = $this->compileWheres($ands, $query);

        $query = $this->compileOrWheres($ors, $query);

        // We need to check if the query is already nested, otherwise
        // we'll nest it here and return the result.
        if (!$builder->isNested()) {
            $total = count($ands) + count($raws);

            // Make sure we wrap the query in an 'and' if using
            // multiple filters. We also need to check if only
            // one where is used with multiple orWheres, that
            // we wrap it in an `and` query.
            if ($total > 1 || (count($ands) === 1 && count($ors) > 0)) {
                $query = $this->compileAnd($query);
            }
        }

        return $query;
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
        $bindings = array_filter($bindings, function ($value) {
            return (string) $value !== '';
        });

        return implode('', $bindings);
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
        return $this->wrap($field.Operator::$equals.$value);
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
        return $this->wrap($field.Operator::$greaterThanOrEquals.$value);
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
        return $this->wrap($field.Operator::$lessThanOrEquals.$value);
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
        return $this->wrap($field.Operator::$approximatelyEquals.$value);
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
        return $this->wrap($field.Operator::$equals.$value.Operator::$has);
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
        return $this->wrap($field.Operator::$equals.Operator::$has.$value);
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
        return $this->wrap($field.Operator::$equals.Operator::$has.$value.Operator::$has);
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
        return $this->wrap($field.Operator::$equals.Operator::$has);
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
     * Assembles all where clauses in the current wheres property.
     *
     * @param array  $wheres
     * @param string $query
     *
     * @return string
     */
    protected function compileWheres(array $wheres = [], $query = '')
    {
        foreach ($wheres as $where) {
            $query .= $this->compileWhere($where);
        }

        return $query;
    }

    /**
     * Assembles all or where clauses in the current orWheres property.
     *
     * @param array  $orWheres
     * @param string $query
     *
     * @return string
     */
    protected function compileOrWheres(array $orWheres = [], $query = '')
    {
        $or = '';

        foreach ($orWheres as $where) {
            $or .= $this->compileWhere($where);
        }

        // Make sure we wrap the query in an 'or' if using multiple
        // orWheres. For example (|(QUERY)(ORWHEREQUERY)).
        if (($query && count($orWheres) > 0) || count($orWheres) > 1) {
            $query .= $this->compileOr($or);
        } else {
            $query .= $or;
        }

        return $query;
    }

    /**
     * Assembles a single where query based
     * on its operator and returns it.
     *
     * @param array $where
     *
     * @return string|null
     */
    protected function compileWhere(array $where)
    {
        // Get the name of the operator.
        if ($name = array_search($where['operator'], Operator::all())) {
            // If the name was found we'll camel case it
            // to run it through the compile method.
            $method = 'compile'.ucfirst($name);

            // Make sure the compile method exists for the operator.
            if (method_exists($this, $method)) {
                return $this->{$method}($where['field'], $where['value']);
            }
        }
    }
}
