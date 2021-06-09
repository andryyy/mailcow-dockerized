<?php

namespace LdapRecord\Query\Pagination;

use LdapRecord\LdapInterface;

class Paginator extends AbstractPaginator
{
    /**
     * @inheritdoc
     */
    protected function fetchCookie()
    {
        return $this->query->controls[LDAP_CONTROL_PAGEDRESULTS]['value']['cookie'] ?? null;
    }

    /**
     * @inheritdoc
     */
    protected function prepareServerControls()
    {
        $this->query->addControl(LDAP_CONTROL_PAGEDRESULTS, $this->isCritical, [
            'size' => $this->perPage, 'cookie' => '',
        ]);
    }

    /**
     * @inheritdoc
     */
    protected function applyServerControls(LdapInterface $ldap)
    {
        $ldap->setOption(LDAP_OPT_SERVER_CONTROLS, $this->query->controls);
    }

    /**
     * @inheritdoc
     */
    protected function updateServerControls(LdapInterface $ldap, $resource)
    {
        $errorCode = $dn = $errorMessage = $refs = null;

        $ldap->parseResult(
            $resource,
            $errorCode,
            $dn,
            $errorMessage,
            $refs,
            $this->query->controls
        );

        $this->resetPageSize();
    }

    /**
     * Reset the page control page size.
     *
     * @return void
     */
    protected function resetPageSize()
    {
        $this->query->controls[LDAP_CONTROL_PAGEDRESULTS]['value']['size'] = $this->perPage;
    }

    /**
     * @inheritdoc
     */
    protected function resetServerControls(LdapInterface $ldap)
    {
        $this->query->controls = [];
    }
}
