<?php

namespace LdapRecord\Models;

use InvalidArgumentException;

class BatchModification
{
    use DetectsResetIntegers;

    /**
     * The array keys to be used in batch modifications.
     */
    const KEY_ATTRIB = 'attrib';
    const KEY_MODTYPE = 'modtype';
    const KEY_VALUES = 'values';

    /**
     * The attribute of the modification.
     *
     * @var string|null
     */
    protected $attribute;

    /**
     * The original value of the attribute before modification.
     *
     * @var array
     */
    protected $original = [];

    /**
     * The values of the modification.
     *
     * @var array
     */
    protected $values = [];

    /**
     * The modtype integer of the batch modification.
     *
     * @var int|null
     */
    protected $type;

    /**
     * Constructor.
     *
     * @param string|null     $attribute
     * @param string|int|null $type
     * @param array           $values
     */
    public function __construct($attribute = null, $type = null, array $values = [])
    {
        $this->setAttribute($attribute)
            ->setType($type)
            ->setValues($values);
    }

    /**
     * Set the original value of the attribute before modification.
     *
     * @param array|string $original
     *
     * @return $this
     */
    public function setOriginal($original = [])
    {
        $this->original = $this->normalizeAttributeValues($original);

        return $this;
    }

    /**
     * Returns the original value of the attribute before modification.
     *
     * @return array
     */
    public function getOriginal()
    {
        return $this->original;
    }

    /**
     * Set the attribute of the modification.
     *
     * @param string $attribute
     *
     * @return $this
     */
    public function setAttribute($attribute)
    {
        $this->attribute = $attribute;

        return $this;
    }

    /**
     * Returns the attribute of the modification.
     *
     * @return string
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * Set the values of the modification.
     *
     * @param array $values
     *
     * @return $this
     */
    public function setValues(array $values = [])
    {
        // Null and empty values must also not be added to a batch
        // modification. Passing null or empty values will result
        // in an exception when trying to save the modification.
        $this->values = array_filter($this->normalizeAttributeValues($values), function ($value) {
            return is_numeric($value) && $this->valueIsResetInteger((int) $value) ?: ! empty($value);
        });

        return $this;
    }

    /**
     * Normalize all of the attribute values.
     *
     * @param array|string $values
     *
     * @return array
     */
    protected function normalizeAttributeValues($values = [])
    {
        // We must convert all of the values to strings. Only strings can
        // be used in batch modifications, otherwise we will we will
        // receive an LDAP exception while attempting to save.
        return array_map('strval', (array) $values);
    }

    /**
     * Returns the values of the modification.
     *
     * @return array
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * Set the type of the modification.
     *
     * @param int|null $type
     *
     * @return $this
     */
    public function setType($type = null)
    {
        if (is_null($type)) {
            return $this;
        }

        if (! $this->isValidType($type)) {
            throw new InvalidArgumentException('Given batch modification type is invalid.');
        }

        $this->type = $type;

        return $this;
    }

    /**
     * Returns the type of the modification.
     *
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Determines if the batch modification is valid in its current state.
     *
     * @return bool
     */
    public function isValid()
    {
        return ! is_null($this->get());
    }

    /**
     * Builds the type of modification automatically
     * based on the current and original values.
     *
     * @return $this
     */
    public function build()
    {
        switch (true) {
            case empty($this->original) && empty($this->values):
                return $this;
            case ! empty($this->original) && empty($this->values):
                return $this->setType(LDAP_MODIFY_BATCH_REMOVE_ALL);
            case empty($this->original) && ! empty($this->values):
                return $this->setType(LDAP_MODIFY_BATCH_ADD);
            default:
               return $this->determineBatchTypeFromOriginal();
        }
    }

    /**
     * Determine the batch modification type from the original values.
     *
     * @return $this
     */
    protected function determineBatchTypeFromOriginal()
    {
        $added = $this->getAddedValues();
        $removed = $this->getRemovedValues();

        switch (true) {
            case ! empty($added) && ! empty($removed):
                return $this->setType(LDAP_MODIFY_BATCH_REPLACE);
            case ! empty($added):
                return $this->setValues($added)->setType(LDAP_MODIFY_BATCH_ADD);
            case ! empty($removed):
                return $this->setValues($removed)->setType(LDAP_MODIFY_BATCH_REMOVE);
            default:
                return $this;
        }
    }

    /**
     * Get the values that were added to the attribute.
     *
     * @return array
     */
    protected function getAddedValues()
    {
        return array_values(
            array_diff($this->values, $this->original)
        );
    }

    /**
     * Get the values that were removed from the attribute.
     *
     * @return array
     */
    protected function getRemovedValues()
    {
        return array_values(
            array_diff($this->original, $this->values)
        );
    }

    /**
     * Returns the built batch modification array.
     *
     * @return array|null
     */
    public function get()
    {
        switch ($this->type) {
            case LDAP_MODIFY_BATCH_REMOVE_ALL:
                // A values key cannot be provided when
                // a remove all type is selected.
                return [
                    static::KEY_ATTRIB  => $this->attribute,
                    static::KEY_MODTYPE => $this->type,
                ];
            case LDAP_MODIFY_BATCH_REMOVE:
                // Fallthrough.
            case LDAP_MODIFY_BATCH_ADD:
                // Fallthrough.
            case LDAP_MODIFY_BATCH_REPLACE:
                return [
                    static::KEY_ATTRIB  => $this->attribute,
                    static::KEY_MODTYPE => $this->type,
                    static::KEY_VALUES  => $this->values,
                ];
            default:
                // If the modtype isn't recognized, we'll return null.
                return;
        }
    }

    /**
     * Determines if the given modtype is valid.
     *
     * @param int $type
     *
     * @return bool
     */
    protected function isValidType($type)
    {
        return in_array($type, [
            LDAP_MODIFY_BATCH_REMOVE_ALL,
            LDAP_MODIFY_BATCH_REMOVE,
            LDAP_MODIFY_BATCH_REPLACE,
            LDAP_MODIFY_BATCH_ADD,
        ]);
    }
}
