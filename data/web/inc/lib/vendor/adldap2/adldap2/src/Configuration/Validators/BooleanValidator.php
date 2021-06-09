<?php

namespace Adldap\Configuration\Validators;

use Adldap\Configuration\ConfigurationException;

/**
 * Class BooleanValidator.
 *
 * Validates that the configuration value is a boolean.
 */
class BooleanValidator extends Validator
{
    /**
     * {@inheritdoc}
     */
    public function validate()
    {
        if (!is_bool($this->value)) {
            throw new ConfigurationException("Option {$this->key} must be a boolean.");
        }

        return true;
    }
}
