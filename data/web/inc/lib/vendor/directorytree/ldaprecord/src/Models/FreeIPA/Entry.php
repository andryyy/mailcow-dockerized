<?php

namespace LdapRecord\Models\FreeIPA;

use LdapRecord\Connection;
use LdapRecord\Models\Entry as BaseEntry;
use LdapRecord\Models\FreeIPA\Scopes\AddEntryUuidToSelects;
use LdapRecord\Models\Types\FreeIPA;
use LdapRecord\Query\Model\FreeIpaBuilder;

/** @mixin FreeIpaBuilder */
class Entry extends BaseEntry implements FreeIPA
{
    /**
     * The attribute key that contains the models object GUID.
     *
     * @var string
     */
    protected $guidKey = 'ipauniqueid';

    /**
     * The default attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $defaultDates = [
        'krblastpwdchange'      => 'ldap',
        'krbpasswordexpiration' => 'ldap',
    ];

    /**
     * @inheritdoc
     */
    protected static function boot()
    {
        parent::boot();

        // Here we'll add a global scope to all FreeIPA models to ensure the
        // Entry UUID is always selected on each query. This attribute is
        // virtual, so it must be manually selected to be included.
        static::addGlobalScope(new AddEntryUuidToSelects());
    }

    /**
     * Create a new query builder.
     *
     * @param Connection $connection
     *
     * @return FreeIpaBuilder
     */
    public function newQueryBuilder(Connection $connection)
    {
        return new FreeIpaBuilder($connection);
    }
}
