<?php

namespace Adldap\Models;

use InvalidArgumentException;

/**
 * Class BatchModification.
 *
 * A utility class to assist in the creation of LDAP
 * batch modifications and ensure their validity.
 */
class BatchModification
{
    /**
     * The array keys to be used in batch modifications.
     */
    const KEY_ATTRIB = 'attrib';
    const KEY_MODTYPE = 'modtype';
    const KEY_VALUES = 'values';

    /**
     * The original value of the attribute before modification.
     *
     * @var null
     */
    protected $original = null;

    /**
     * The attribute of the modification.
     *
     * @var int|string
     */
    protected $attribute;

    /**
     * The values of the modification.
     *
     * @var array
     */
    protected $values = [];

    /**
     * The modtype integer of the batch modification.
     *
     * @var int
     */
    protected $type;

    /**
     * Constructor.
     *
     * @param string|null     $attribute
     * @param string|int|null $type
     * @param array           $values
     */
    public function __construct($attribute = null, $type = null, $values = [])
    {
        $this->setAttribute($attribute)
            ->setType($type)
            ->setValues($values);
    }

    /**
     * Sets the original value of the attribute before modification.
     *
     * @param mixed $original
     *
     * @return $this
     */
    public function setOriginal($original = null)
    {
        $this->original = $original;

        return $this;
    }

    /**
     * Returns the original value of the attribute before modification.
     *
     * @return mixed
     */
    public function getOriginal()
    {
        return $this->original;
    }

    /**
     * Sets the attribute of the modification.
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
     * Sets the values of the modification.
     *
     * @param array $values
     *
     * @return $this
     */
    public function setValues(array $values = [])
    {
        $this->values = array_map(function ($value) {
            // We need to make sure all values given to a batch modification are
            // strings, otherwise we'll receive an LDAP exception when
            // we try to process the modification.
            return (string) $value;
        }, $values);

        return $this;
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
     * Sets the type of the modification.
     *
     * @param int|null $type
     *
     * @return $this
     */
    public function setType($type = null)
    {
        if (!is_null($type) && !$this->isValidType($type)) {
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
     * Determines if the batch modification
     * is valid in its current state.
     *
     * @return bool
     */
    public function isValid()
    {
        return !is_null($this->get());
    }

    /**
     * Builds the type of modification automatically
     * based on the current and original values.
     *
     * @return $this
     */
    public function build()
    {
        $filtered = array_diff(
            array_map('trim', $this->values),
            ['']
        );

        if (is_null($this->original)) {
            // If the original value is null, we'll assume
            // that the attribute doesn't exist yet.
            if (!empty($filtered)) {
                // If the filtered array is not empty, we can
                // assume we're looking to add values
                // to the current attribute.
                $this->setType(LDAP_MODIFY_BATCH_ADD);
            }

            // If the filtered array is empty and there is no original
            // value, then we can ignore this attribute since
            // we can't push null values to the server.
        } else {
            if (empty($filtered)) {
                // If there's an original value and the array is
                // empty then we can assume we are looking
                // to completely remove all values
                // of the current attribute.
                $this->setType(LDAP_MODIFY_BATCH_REMOVE_ALL);
            } else {
                // If the array isn't empty then we can assume
                // we're looking to replace all attributes.
                $this->setType(LDAP_MODIFY_BATCH_REPLACE);
            }
        }

        return $this;
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
