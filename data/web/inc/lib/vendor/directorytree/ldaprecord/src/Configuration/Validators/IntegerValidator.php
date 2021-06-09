<?php

namespace LdapRecord\Configuration\Validators;

use LdapRecord\Configuration\ConfigurationException;

class IntegerValidator extends Validator
{
    /**
     * @inheritdoc
     */
    public function validate()
    {
        if (! is_numeric($this->value)) {
            throw new ConfigurationException("Option {$this->key} must be an integer.");
        }

        return true;
    }
}
