<?php

namespace LdapRecord\Models\Attributes;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use DateTime;
use LdapRecord\LdapRecordException;
use LdapRecord\Utilities;

class Timestamp
{
    /**
     * The current timestamp type.
     *
     * @var string
     */
    protected $type;

    /**
     * The available timestamp types.
     *
     * @var array
     */
    protected $types = [
        'ldap',
        'windows',
        'windows-int',
    ];

    /**
     * Constructor.
     *
     * @param string $type
     *
     * @throws LdapRecordException
     */
    public function __construct($type)
    {
        $this->setType($type);
    }

    /**
     * Set the type of timestamp to convert from / to.
     *
     * @param string $type
     *
     * @throws LdapRecordException
     */
    public function setType($type)
    {
        if (! in_array($type, $this->types)) {
            throw new LdapRecordException("Unrecognized LDAP date type [$type]");
        }

        $this->type = $type;
    }

    /**
     * Converts the value to an LDAP date string.
     *
     * @param mixed $value
     *
     * @return float|string
     *
     * @throws LdapRecordException
     */
    public function fromDateTime($value)
    {
        $value = is_array($value) ? reset($value) : $value;

        // If the value is being converted to a windows integer format but it
        // is already in that format, we will simply return the value back.
        if ($this->type == 'windows-int' && $this->valueIsWindowsIntegerType($value)) {
            return $value;
        }
        // If the value is numeric, we will assume it's a UNIX timestamp.
        elseif (is_numeric($value)) {
            $value = Carbon::createFromTimestamp($value);
        }
        // If a string is given, we will pass it into a new carbon instance.
        elseif (is_string($value)) {
            $value = Carbon::parse($value);
        }
        // If a date object is given, we will convert it to a carbon instance.
        elseif ($value instanceof DateTime) {
            $value = Carbon::instance($value);
        }

        switch ($this->type) {
            case 'ldap':
                $value = $this->convertDateTimeToLdapTime($value);
                break;
            case 'windows':
                $value = $this->convertDateTimeToWindows($value);
                break;
            case 'windows-int':
                $value = $this->convertDateTimeToWindowsInteger($value);
                break;
            default:
                throw new LdapRecordException("Unrecognized date type [{$this->type}]");
        }

        return $value;
    }

    /**
     * Determine if the value given is in Windows Integer (NTFS Filetime) format.
     *
     * @param int|string $value
     *
     * @return bool
     */
    protected function valueIsWindowsIntegerType($value)
    {
        return is_numeric($value) && strlen((string) $value) === 18;
    }

    /**
     * Converts the LDAP timestamp value to a Carbon instance.
     *
     * @param mixed $value
     *
     * @return Carbon|false
     *
     * @throws LdapRecordException
     */
    public function toDateTime($value)
    {
        $value = is_array($value) ? reset($value) : $value;

        if ($value instanceof CarbonInterface || $value instanceof DateTime) {
            return Carbon::instance($value);
        }

        switch ($this->type) {
            case 'ldap':
                $value = $this->convertLdapTimeToDateTime($value);
                break;
            case 'windows':
                $value = $this->convertWindowsTimeToDateTime($value);
                break;
            case 'windows-int':
                $value = $this->convertWindowsIntegerTimeToDateTime($value);
                break;
            default:
                throw new LdapRecordException("Unrecognized date type [{$this->type}]");
        }

        return $value instanceof DateTime ? Carbon::instance($value) : $value;
    }

    /**
     * Converts standard LDAP timestamps to a date time object.
     *
     * @param string $value
     *
     * @return DateTime|bool
     */
    protected function convertLdapTimeToDateTime($value)
    {
        return DateTime::createFromFormat(
            strpos($value, 'Z') !== false ? 'YmdHis\Z' : 'YmdHisT',
            $value
        );
    }

    /**
     * Converts date objects to a standard LDAP timestamp.
     *
     * @param DateTime $date
     *
     * @return string
     */
    protected function convertDateTimeToLdapTime(DateTime $date)
    {
        return $date->format(
            $date->getOffset() == 0 ? 'YmdHis\Z' : 'YmdHisO'
        );
    }

    /**
     * Converts standard windows timestamps to a date time object.
     *
     * @param string $value
     *
     * @return DateTime|bool
     */
    protected function convertWindowsTimeToDateTime($value)
    {
        return DateTime::createFromFormat(
            strpos($value, '0Z') !== false ? 'YmdHis.0\Z' : 'YmdHis.0T',
            $value
        );
    }

    /**
     * Converts date objects to a windows timestamp.
     *
     * @param DateTime $date
     *
     * @return string
     */
    protected function convertDateTimeToWindows(DateTime $date)
    {
        return $date->format(
            $date->getOffset() == 0 ? 'YmdHis.0\Z' : 'YmdHis.0O'
        );
    }

    /**
     * Converts standard windows integer dates to a date time object.
     *
     * @param int $value
     *
     * @return DateTime|bool
     *
     * @throws \Exception
     */
    protected function convertWindowsIntegerTimeToDateTime($value)
    {
        // ActiveDirectory dates that contain integers may return
        // "0" when they are not set. We will validate that here.
        if (! $value) {
            return false;
        }

        return (new DateTime())->setTimestamp(
            Utilities::convertWindowsTimeToUnixTime($value)
        );
    }

    /**
     * Converts date objects to a windows integer timestamp.
     *
     * @param DateTime $date
     *
     * @return float
     */
    protected function convertDateTimeToWindowsInteger(DateTime $date)
    {
        return Utilities::convertUnixTimeToWindowsTime($date->getTimestamp());
    }
}
