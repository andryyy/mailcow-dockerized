<?php

namespace LdapRecord\Models\Attributes;

class TSProperty
{
    /**
     * Nibble control values. The first value for each is if the nibble is <= 9, otherwise the second value is used.
     */
    const NIBBLE_CONTROL = [
        'X' => ['001011', '011010'],
        'Y' => ['001110', '011010'],
    ];

    /**
     * The nibble header.
     */
    const NIBBLE_HEADER = '1110';

    /**
     * Conversion factor needed for time values in the TSPropertyArray (stored in microseconds).
     */
    const TIME_CONVERSION = 60 * 1000;

    /**
     * A simple map to help determine how the property needs to be decoded/encoded from/to its binary value.
     *
     * There are some names that are simple repeats but have 'W' at the end. Not sure as to what that signifies. I
     * cannot find any information on them in Microsoft documentation. However, their values appear to stay in sync with
     * their non 'W' counterparts. But not doing so when manipulating the data manually does not seem to affect anything.
     * This probably needs more investigation.
     *
     * @var array
     */
    protected $propTypes = [
        'string' => [
            'CtxWFHomeDir',
            'CtxWFHomeDirW',
            'CtxWFHomeDirDrive',
            'CtxWFHomeDirDriveW',
            'CtxInitialProgram',
            'CtxInitialProgramW',
            'CtxWFProfilePath',
            'CtxWFProfilePathW',
            'CtxWorkDirectory',
            'CtxWorkDirectoryW',
            'CtxCallbackNumber',
        ],
        'time' => [
            'CtxMaxDisconnectionTime',
            'CtxMaxConnectionTime',
            'CtxMaxIdleTime',
        ],
        'int' => [
            'CtxCfgFlags1',
            'CtxCfgPresent',
            'CtxKeyboardLayout',
            'CtxMinEncryptionLevel',
            'CtxNWLogonServer',
            'CtxShadow',
        ],
    ];

    /**
     * The property name.
     *
     * @var string
     */
    protected $name;

    /**
     * The property value.
     *
     * @var string|int
     */
    protected $value;

    /**
     * The property value type.
     *
     * @var int
     */
    protected $valueType = 1;

    /**
     * Pass binary TSProperty data to construct its object representation.
     *
     * @param string|null $value
     */
    public function __construct($value = null)
    {
        if ($value) {
            $this->decode(bin2hex($value));
        }
    }

    /**
     * Set the name for the TSProperty.
     *
     * @param string $name
     *
     * @return TSProperty
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the name for the TSProperty.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the value for the TSProperty.
     *
     * @param string|int $value
     *
     * @return TSProperty
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get the value for the TSProperty.
     *
     * @return string|int
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Convert the TSProperty name/value back to its binary
     * representation for the userParameters blob.
     *
     * @return string
     */
    public function toBinary()
    {
        $name = bin2hex($this->name);

        $binValue = $this->getEncodedValueForProp($this->name, $this->value);

        $valueLen = strlen(bin2hex($binValue)) / 3;

        $binary = hex2bin(
            $this->dec2hex(strlen($name))
            .$this->dec2hex($valueLen)
            .$this->dec2hex($this->valueType)
            .$name
        );

        return $binary.$binValue;
    }

    /**
     * Given a TSProperty blob, decode the name/value/type/etc.
     *
     * @param string $tsProperty
     */
    protected function decode($tsProperty)
    {
        $nameLength = hexdec(substr($tsProperty, 0, 2));

        // 1 data byte is 3 encoded bytes
        $valueLength = hexdec(substr($tsProperty, 2, 2)) * 3;

        $this->valueType = hexdec(substr($tsProperty, 4, 2));
        $this->name = pack('H*', substr($tsProperty, 6, $nameLength));
        $this->value = $this->getDecodedValueForProp($this->name, substr($tsProperty, 6 + $nameLength, $valueLength));
    }

    /**
     * Based on the property name/value in question, get its encoded form.
     *
     * @param string     $propName
     * @param string|int $propValue
     *
     * @return string
     */
    protected function getEncodedValueForProp($propName, $propValue)
    {
        if (in_array($propName, $this->propTypes['string'])) {
            // Simple strings are null terminated. Unsure if this is
            // needed or simply a product of how ADUC does stuff?
            $value = $this->encodePropValue($propValue."\0", true);
        } elseif (in_array($propName, $this->propTypes['time'])) {
            // Needs to be in microseconds (assuming it is in minute format)...
            $value = $this->encodePropValue($propValue * self::TIME_CONVERSION);
        } else {
            $value = $this->encodePropValue($propValue);
        }

        return $value;
    }

    /**
     * Based on the property name in question, get its actual value from the binary blob value.
     *
     * @param string $propName
     * @param string $propValue
     *
     * @return string|int
     */
    protected function getDecodedValueForProp($propName, $propValue)
    {
        if (in_array($propName, $this->propTypes['string'])) {
            // Strip away null terminators. I think this should
            // be desired, otherwise it just ends in confusion.
            $value = str_replace("\0", '', $this->decodePropValue($propValue, true));
        } elseif (in_array($propName, $this->propTypes['time'])) {
            // Convert from microseconds to minutes (how ADUC displays
            // it anyway, and seems the most practical).
            $value = hexdec($this->decodePropValue($propValue)) / self::TIME_CONVERSION;
        } elseif (in_array($propName, $this->propTypes['int'])) {
            $value = hexdec($this->decodePropValue($propValue));
        } else {
            $value = $this->decodePropValue($propValue);
        }

        return $value;
    }

    /**
     * Decode the property by inspecting the nibbles of each blob, checking
     * the control, and adding up the results into a final value.
     *
     * @param string $hex
     * @param bool   $string Whether or not this is simple string data.
     *
     * @return string
     */
    protected function decodePropValue($hex, $string = false)
    {
        $decodePropValue = '';

        $blobs = str_split($hex, 6);

        foreach ($blobs as $blob) {
            $bin = decbin(hexdec($blob));

            $controlY = substr($bin, 4, 6);
            $nibbleY = substr($bin, 10, 4);
            $controlX = substr($bin, 14, 6);
            $nibbleX = substr($bin, 20, 4);

            $byte = $this->nibbleControl($nibbleX, $controlX).$this->nibbleControl($nibbleY, $controlY);

            if ($string) {
                $decodePropValue .= MbString::chr(bindec($byte));
            } else {
                $decodePropValue = $this->dec2hex(bindec($byte)).$decodePropValue;
            }
        }

        return $decodePropValue;
    }

    /**
     * Get the encoded property value as a binary blob.
     *
     * @param string $value
     * @param bool   $string
     *
     * @return string
     */
    protected function encodePropValue($value, $string = false)
    {
        // An int must be properly padded. (then split and reversed).
        // For a string, we just split the chars. This seems
        // to be the easiest way to handle UTF-8 characters
        // instead of trying to work with their hex values.
        $chars = $string ? MbString::split($value) : array_reverse(str_split($this->dec2hex($value, 8), 2));

        $encoded = '';

        foreach ($chars as $char) {
            // Get the bits for the char. Using this method to ensure it is fully padded.
            $bits = sprintf('%08b', $string ? MbString::ord($char) : hexdec($char));
            $nibbleX = substr($bits, 0, 4);
            $nibbleY = substr($bits, 4, 4);

            // Construct the value with the header, high nibble, then low nibble.
            $value = self::NIBBLE_HEADER;

            foreach (['Y' => $nibbleY, 'X' => $nibbleX] as $nibbleType => $nibble) {
                $value .= $this->getNibbleWithControl($nibbleType, $nibble);
            }

            // Convert it back to a binary bit stream
            foreach ([0, 8, 16] as $start) {
                $encoded .= $this->packBitString(substr($value, $start, 8), 8);
            }
        }

        return $encoded;
    }

    /**
     * PHP's pack() function has no 'b' or 'B' template. This is
     * a workaround that turns a literal bit-string into a
     * packed byte-string with 8 bits per byte.
     *
     * @param string $bits
     * @param bool   $len
     *
     * @return string
     */
    protected function packBitString($bits, $len)
    {
        $bits = substr($bits, 0, $len);
        // Pad input with zeros to next multiple of 4 above $len
        $bits = str_pad($bits, 4 * (int) (($len + 3) / 4), '0');

        // Split input into chunks of 4 bits, convert each to hex and pack them
        $nibbles = str_split($bits, 4);
        foreach ($nibbles as $i => $nibble) {
            $nibbles[$i] = base_convert($nibble, 2, 16);
        }

        return pack('H*', implode('', $nibbles));
    }

    /**
     * Based on the control, adjust the nibble accordingly.
     *
     * @param string $nibble
     * @param string $control
     *
     * @return string
     */
    protected function nibbleControl($nibble, $control)
    {
        // This control stays constant for the low/high nibbles,
        // so it doesn't matter which we compare to
        if ($control == self::NIBBLE_CONTROL['X'][1]) {
            $dec = bindec($nibble);
            $dec += 9;
            $nibble = str_pad(decbin($dec), 4, '0', STR_PAD_LEFT);
        }

        return $nibble;
    }

    /**
     * Get the nibble value with the control prefixed.
     *
     * If the nibble dec is <= 9, the control X equals 001011 and Y equals 001110, otherwise if the nibble dec is > 9
     * the control for X or Y equals 011010. Additionally, if the dec value of the nibble is > 9, then the nibble value
     * must be subtracted by 9 before the final value is constructed.
     *
     * @param string $nibbleType Either X or Y
     * @param string $nibble
     *
     * @return string
     */
    protected function getNibbleWithControl($nibbleType, $nibble)
    {
        $dec = bindec($nibble);

        if ($dec > 9) {
            $dec -= 9;
            $control = self::NIBBLE_CONTROL[$nibbleType][1];
        } else {
            $control = self::NIBBLE_CONTROL[$nibbleType][0];
        }

        return $control.sprintf('%04d', decbin($dec));
    }

    /**
     * Need to make sure hex values are always an even length, so pad as needed.
     *
     * @param int $int
     * @param int $padLength The hex string must be padded to this length (with zeros).
     *
     * @return string
     */
    protected function dec2hex($int, $padLength = 2)
    {
        return str_pad(dechex($int), $padLength, 0, STR_PAD_LEFT);
    }
}
