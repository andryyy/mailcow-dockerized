<?php

namespace LdapRecord\Models\Attributes;

class MbString
{
    /**
     * Get the integer value of a specific character.
     *
     * @param $string
     *
     * @return int
     */
    public static function ord($string)
    {
        if (static::isLoaded()) {
            $result = unpack('N', mb_convert_encoding($string, 'UCS-4BE', 'UTF-8'));

            if (is_array($result)) {
                return $result[1];
            }
        }

        return ord($string);
    }

    /**
     * Get the character for a specific integer value.
     *
     * @param $int
     *
     * @return string
     */
    public static function chr($int)
    {
        if (static::isLoaded()) {
            return mb_convert_encoding(pack('n', $int), 'UTF-8', 'UTF-16BE');
        }

        return chr($int);
    }

    /**
     * Split a string into its individual characters and return it as an array.
     *
     * @param string $value
     *
     * @return string[]
     */
    public static function split($value)
    {
        return preg_split('/(?<!^)(?!$)/u', $value);
    }

    /**
     * Detects if the given string is UTF 8.
     *
     * @param $string
     *
     * @return string|false
     */
    public static function isUtf8($string)
    {
        if (static::isLoaded()) {
            return mb_detect_encoding($string, 'UTF-8', $strict = true);
        }

        return $string;
    }

    /**
     * Checks if the mbstring extension is enabled in PHP.
     *
     * @return bool
     */
    public static function isLoaded()
    {
        return extension_loaded('mbstring');
    }
}
