<?php

namespace Adldap\Configuration\Validators;

use Adldap\Configuration\ConfigurationException;

class ClassValidator extends Validator
{
    /**
     * Validates the configuration value.
     *
     * @throws ConfigurationException When the value given fails validation.
     *
     * @return bool
     */
    public function validate()
    {
        if (!class_exists($this->value)) {
            throw new ConfigurationException("Option {$this->key} must be a valid class.");
        }

        return true;
    }
}
