<?php

namespace LdapRecord\Query\Pagination;

use LdapRecord\LdapInterface;
use LdapRecord\Query\Builder;

abstract class AbstractPaginator
{
    /**
     * The query builder instance.
     *
     * @var Builder
     */
    protected $query;

    /**
     * The filter to execute.
     *
     * @var string
     */
    protected $filter;

    /**
     * The amount of objects to fetch per page.
     *
     * @var int
     */
    protected $perPage;

    /**
     * Whether the operation is critical.
     *
     * @var bool
     */
    protected $isCritical;

    /**
     * Constructor.
     *
     * @param Builder $query
     */
    public function __construct(Builder $query, $filter, $perPage, $isCritical)
    {
        $this->query = $query;
        $this->filter = $filter;
        $this->perPage = $perPage;
        $this->isCritical = $isCritical;
    }

    /**
     * Execute the pagination request.
     *
     * @param LdapInterface $ldap
     *
     * @return array
     */
    public function execute(LdapInterface $ldap)
    {
        $pages = [];

        $this->prepareServerControls();

        do {
            $this->applyServerControls($ldap);

            if (! $resource = $this->query->run($this->filter)) {
                break;
            }

            $this->updateServerControls($ldap, $resource);

            $pages[] = $this->query->parse($resource);
        } while (! empty($this->fetchCookie()));

        $this->resetServerControls($ldap);

        return $pages;
    }

    /**
     * Fetch the pagination cookie.
     *
     * @return string
     */
    abstract protected function fetchCookie();

    /**
     * Prepare the server controls before executing the pagination request.
     *
     * @return void
     */
    abstract protected function prepareServerControls();

    /**
     * Apply the server controls.
     *
     * @param LdapInterface $ldap
     *
     * @return void
     */
    abstract protected function applyServerControls(LdapInterface $ldap);

    /**
     * Reset the server controls.
     *
     * @param LdapInterface $ldap
     *
     * @return mixed
     */
    abstract protected function resetServerControls(LdapInterface $ldap);

    /**
     * Update the server controls.
     *
     * @param LdapInterface $ldap
     * @param resource      $resource
     *
     * @return void
     */
    abstract protected function updateServerControls(LdapInterface $ldap, $resource);
}
