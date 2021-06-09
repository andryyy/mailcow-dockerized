<?php

namespace Adldap\Configuration\Validators;

use Adldap\Configuration\ConfigurationException;

/**
 * Class StringOrNullValidator.
 *
 * Validates that the configuration value is a string or null.
 */
class StringOrNullValidator extends Validator
{
    /**
     * {@inheritdoc}
     */
    public function validate()
    {
        if (is_string($this->value) || is_null($this->value)) {
            return true;
        }

        throw new ConfigurationException("Option {$this->key} must be a string or null.");
    }
}
