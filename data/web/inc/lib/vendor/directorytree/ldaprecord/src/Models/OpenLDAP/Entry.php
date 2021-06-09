<?php

namespace LdapRecord\Models\OpenLDAP;

use LdapRecord\Connection;
use LdapRecord\Models\Entry as BaseEntry;
use LdapRecord\Models\OpenLDAP\Scopes\AddEntryUuidToSelects;
use LdapRecord\Models\Types\OpenLDAP;
use LdapRecord\Query\Model\OpenLdapBuilder;

/** @mixin OpenLdapBuilder */
class Entry extends BaseEntry implements OpenLDAP
{
    /**
     * The attribute key that contains the models object GUID.
     *
     * @var string
     */
    protected $guidKey = 'entryuuid';

    /**
     * @inheritdoc
     */
    protected static function boot()
    {
        parent::boot();

        // Here we'll add a global scope to all OpenLDAP models to ensure the
        // Entry UUID is always selected on each query. This attribute is
        // virtual, so it must be manually selected to be included.
        static::addGlobalScope(new AddEntryUuidToSelects());
    }

    /**
     * Create a new query builder.
     *
     * @param Connection $connection
     *
     * @return OpenLdapBuilder
     */
    public function newQueryBuilder(Connection $connection)
    {
        return new OpenLdapBuilder($connection);
    }
}
