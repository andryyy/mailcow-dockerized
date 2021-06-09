<?php

namespace LdapRecord\Models\ActiveDirectory;

class ExchangeDatabase extends Entry
{
    /**
     * @inheritdoc
     */
    public static $objectClasses = ['msExchMDB'];

    /**
     * @inheritdoc
     */
    public static function boot()
    {
        parent::boot();

        static::addGlobalScope(new Scopes\InConfigurationContext);
    }
}
