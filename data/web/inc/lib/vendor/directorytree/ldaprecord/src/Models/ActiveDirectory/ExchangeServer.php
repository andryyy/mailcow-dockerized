<?php

namespace LdapRecord\Models\ActiveDirectory;

class ExchangeServer extends Entry
{
    /**
     * @inheritdoc
     */
    public static $objectClasses = ['msExchExchangeServer'];

    /**
     * @inheritdoc
     */
    public static function boot()
    {
        parent::boot();

        static::addGlobalScope(new Scopes\HasServerRoleAttribute);
        static::addGlobalScope(new Scopes\InConfigurationContext);
    }
}
