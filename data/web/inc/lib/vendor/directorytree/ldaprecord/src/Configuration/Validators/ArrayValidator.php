<?php

namespace LdapRecord\Configuration\Validators;

use LdapRecord\Configuration\ConfigurationException;

class ArrayValidator extends Validator
{
    /**
     * @inheritdoc
     */
    public function validate()
    {
        if (! is_array($this->value)) {
            throw new ConfigurationException("Option {$this->key} must be an array.");
        }

        return true;
    }
}
