<?php

namespace LdapRecord\Configuration\Validators;

use LdapRecord\Configuration\ConfigurationException;

class BooleanValidator extends Validator
{
    /**
     * @inheritdoc
     */
    public function validate()
    {
        if (! is_bool($this->value)) {
            throw new ConfigurationException("Option {$this->key} must be a boolean.");
        }

        return true;
    }
}
