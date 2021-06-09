<?php

namespace LdapRecord\Query\Pagination;

use LdapRecord\LdapInterface;

class DeprecatedPaginator extends AbstractPaginator
{
    /**
     * The pagination cookie.
     *
     * @var string
     */
    protected $cookie = '';

    /**
     * @inheritdoc
     */
    protected function fetchCookie()
    {
        return $this->cookie;
    }

    /**
     * @inheritdoc
     */
    protected function prepareServerControls()
    {
        $this->cookie = '';
    }

    /**
     * @inheritdoc
     */
    protected function applyServerControls(LdapInterface $ldap)
    {
        $ldap->controlPagedResult($this->perPage, $this->isCritical, $this->cookie);
    }

    /**
     * @inheritdoc
     */
    protected function updateServerControls(LdapInterface $ldap, $resource)
    {
        $ldap->controlPagedResultResponse($resource, $this->cookie);
    }

    /**
     * @inheritdoc
     */
    protected function resetServerControls(LdapInterface $ldap)
    {
        $ldap->controlPagedResult();
    }
}
