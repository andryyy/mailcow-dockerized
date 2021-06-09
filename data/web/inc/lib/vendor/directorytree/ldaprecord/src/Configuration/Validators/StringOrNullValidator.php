<?php

namespace LdapRecord\Configuration\Validators;

use LdapRecord\Configuration\ConfigurationException;

class StringOrNullValidator extends Validator
{
    /**
     * @inheritdoc
     */
    public function validate()
    {
        if (is_string($this->value) || is_null($this->value)) {
            return true;
        }

        throw new ConfigurationException("Option {$this->key} must be a string or null.");
    }
}
