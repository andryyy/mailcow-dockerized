<?php

namespace LdapRecord\Models\Concerns;

trait HasScopes
{
    /**
     * Begin querying the direct descendants of the model.
     *
     * @return \LdapRecord\Query\Model\Builder
     */
    public function descendants()
    {
        return $this->in($this->getDn())->listing();
    }

    /**
     * Begin querying the direct ancestors of the model.
     *
     * @return \LdapRecord\Query\Model\Builder
     */
    public function ancestors()
    {
        $parent = $this->getParentDn($this->getDn());

        return $this->in($this->getParentDn($parent))->listing();
    }

    /**
     * Begin querying the direct siblings of the model.
     *
     * @return \LdapRecord\Query\Model\Builder
     */
    public function siblings()
    {
        return $this->in($this->getParentDn($this->getDn()))->listing();
    }
}
