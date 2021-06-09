<?php

namespace LdapRecord\Models\Events;

use LdapRecord\Models\Model;

class Renaming extends Event
{
    /**
     * The models RDN.
     *
     * @var string
     */
    protected $rdn;

    /**
     * The models new parent DN.
     *
     * @var string
     */
    protected $newParentDn;

    /**
     * Constructor.
     *
     * @param Model  $model
     * @param string $rdn
     * @param string $newParentDn
     */
    public function __construct(Model $model, $rdn, $newParentDn)
    {
        parent::__construct($model);

        $this->rdn = $rdn;
        $this->newParentDn = $newParentDn;
    }

    /**
     * Get the models RDN.
     *
     * @return string
     */
    public function getRdn()
    {
        return $this->rdn;
    }

    /**
     * Get the models parent DN.
     *
     * @return string
     */
    public function getNewParentDn()
    {
        return $this->newParentDn;
    }
}
