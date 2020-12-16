<?php

namespace WebAuthn\Attestation;
use WebAuthn\WebAuthnException;
use WebAuthn\CBOR\CborDecoder;
use WebAuthn\Binary\ByteBuffer;

/**
 * @author Lukas Buchs
 * @license https://github.com/lbuchs/WebAuthn/blob/master/LICENSE MIT
 */
class AuthenticatorData {
    protected $_binary;
    protected $_rpIdHash;
    protected $_flags;
    protected $_signCount;
    protected $_attestedCredentialData;
    protected $_extensionData;



    // Cose encoded keys
    private static $_COSE_KTY = 1;
    private static $_COSE_ALG = 3;

    // Cose EC2 ES256 P-256 curve
    private static $_COSE_CRV = -1;
    private static $_COSE_X = -2;
    private static $_COSE_Y = -3;

    // Cose RSA PS256
    private static $_COSE_N = -1;
    private static $_COSE_E = -2;

    private static $_EC2_TYPE = 2;
    private static $_EC2_ES256 = -7;
    private static $_EC2_P256 = 1;

    private static $_RSA_TYPE = 3;
    private static $_RSA_RS256 = -257;

    /**
     * Parsing the authenticatorData binary.
     * @param string $binary
     * @throws WebAuthnException
     */
    public function __construct($binary) {
        if (!\is_string($binary) || \strlen($binary) < 37) {
            throw new WebAuthnException('Invalid authenticatorData input', WebAuthnException::INVALID_DATA);
        }
        $this->_binary = $binary;

        // Read infos from binary
        // https://www.w3.org/TR/webauthn/#sec-authenticator-data

        // RP ID
        $this->_rpIdHash = \substr($binary, 0, 32);

        // flags (1 byte)
        $flags = \unpack('Cflags', \substr($binary, 32, 1))['flags'];
        $this->_flags = $this->_readFlags($flags);

        // signature counter: 32-bit unsigned big-endian integer.
        $this->_signCount = \unpack('Nsigncount', \substr($binary, 33, 4))['signcount'];

        $offset = 37;
        // https://www.w3.org/TR/webauthn/#sec-attested-credential-data
        if ($this->_flags->attestedDataIncluded) {
            $this->_attestedCredentialData = $this->_readAttestData($binary, $offset);
        }

        if ($this->_flags->extensionDataIncluded) {
            $this->_readExtensionData(\substr($binary, $offset));
        }
    }

    /**
     * Authenticator Attestation Globally Unique Identifier, a unique number
     * that identifies the model of the authenticator (not the specific instance
     * of the authenticator)
     * The aaguid may be 0 if the user is using a old u2f device and/or if
     * the browser is using the fido-u2f format.
     * @return string
     * @throws WebAuthnException
     */
    public function getAAGUID() {
        if (!($this->_attestedCredentialData instanceof \stdClass)) {
            throw  new WebAuthnException('credential data not included in authenticator data', WebAuthnException::INVALID_DATA);
        }
        return $this->_attestedCredentialData->aaguid;
    }

    /**
     * returns the authenticatorData as binary
     * @return string
     */
    public function getBinary() {
        return $this->_binary;
    }

    /**
     * returns the credentialId
     * @return string
     * @throws WebAuthnException
     */
    public function getCredentialId() {
        if (!($this->_attestedCredentialData instanceof \stdClass)) {
            throw  new WebAuthnException('credential id not included in authenticator data', WebAuthnException::INVALID_DATA);
        }
        return $this->_attestedCredentialData->credentialId;
    }

    /**
     * returns the public key in PEM format
     * @return string
     */
    public function getPublicKeyPem() {
        $der = null;
        switch ($this->_attestedCredentialData->credentialPublicKey->kty) {
            case self::$_EC2_TYPE: $der = $this->_getEc2Der(); break;
            case self::$_RSA_TYPE: $der = $this->_getRsaDer(); break;
            default: throw new WebAuthnException('invalid key type', WebAuthnException::INVALID_DATA);
        }

        $pem = '-----BEGIN PUBLIC KEY-----' . "\n";
        $pem .= \chunk_split(\base64_encode($der), 64, "\n");
        $pem .= '-----END PUBLIC KEY-----' . "\n";
        return $pem;
    }

    /**
     * returns the public key in U2F format
     * @return string
     * @throws WebAuthnException
     */
    public function getPublicKeyU2F() {
        if (!($this->_attestedCredentialData instanceof \stdClass)) {
            throw  new WebAuthnException('credential data not included in authenticator data', WebAuthnException::INVALID_DATA);
        }
        return "\x04" . // ECC uncompressed
                $this->_attestedCredentialData->credentialPublicKey->x .
                $this->_attestedCredentialData->credentialPublicKey->y;
    }

    /**
     * returns the SHA256 hash of the relying party id (=hostname)
     * @return string
     */
    public function getRpIdHash() {
        return $this->_rpIdHash;
    }

    /**
     * returns the sign counter
     * @return int
     */
    public function getSignCount() {
        return $this->_signCount;
    }

    /**
     * returns true if the user is present
     * @return boolean
     */
    public function getUserPresent() {
        return $this->_flags->userPresent;
    }

    /**
     * returns true if the user is verified
     * @return boolean
     */
    public function getUserVerified() {
        return $this->_flags->userVerified;
    }

    // -----------------------------------------------
    // PRIVATE
    // -----------------------------------------------

    /**
     * Returns DER encoded EC2 key
     * @return string
     */
    private function _getEc2Der() {
        return $this->_der_sequence(
            $this->_der_sequence(
                $this->_der_oid("\x2A\x86\x48\xCE\x3D\x02\x01") . // OID 1.2.840.10045.2.1 ecPublicKey
                $this->_der_oid("\x2A\x86\x48\xCE\x3D\x03\x01\x07")  // 1.2.840.10045.3.1.7 prime256v1
            ) .
            $this->_der_bitString($this->getPublicKeyU2F())
        );
    }

    /**
     * Returns DER encoded RSA key
     * @return string
     */
    private function _getRsaDer() {
        return $this->_der_sequence(
            $this->_der_sequence(
                $this->_der_oid("\x2A\x86\x48\x86\xF7\x0D\x01\x01\x01") . // OID 1.2.840.113549.1.1.1 rsaEncryption
                $this->_der_nullValue()
            ) .
            $this->_der_bitString(
                $this->_der_sequence(
                    $this->_der_unsignedInteger($this->_attestedCredentialData->credentialPublicKey->n) .
                    $this->_der_unsignedInteger($this->_attestedCredentialData->credentialPublicKey->e)
                )
            )
        );
    }

    /**
     * reads the flags from flag byte
     * @param string $binFlag
     * @return \stdClass
     */
    private function _readFlags($binFlag) {
        $flags = new \stdClass();

        $flags->bit_0 = !!($binFlag & 1);
        $flags->bit_1 = !!($binFlag & 2);
        $flags->bit_2 = !!($binFlag & 4);
        $flags->bit_3 = !!($binFlag & 8);
        $flags->bit_4 = !!($binFlag & 16);
        $flags->bit_5 = !!($binFlag & 32);
        $flags->bit_6 = !!($binFlag & 64);
        $flags->bit_7 = !!($binFlag & 128);

        // named flags
        $flags->userPresent = $flags->bit_0;
        $flags->userVerified = $flags->bit_2;
        $flags->attestedDataIncluded = $flags->bit_6;
        $flags->extensionDataIncluded = $flags->bit_7;
        return $flags;
    }

    /**
     * read attested data
     * @param string $binary
     * @param int $endOffset
     * @return \stdClass
     * @throws WebAuthnException
     */
    private function _readAttestData($binary, &$endOffset) {
        $attestedCData = new \stdClass();
        if (\strlen($binary) <= 55) {
            throw new WebAuthnException('Attested data should be present but is missing', WebAuthnException::INVALID_DATA);
        }

        // The AAGUID of the authenticator
        $attestedCData->aaguid = \substr($binary, 37, 16);

        //Byte length L of Credential ID, 16-bit unsigned big-endian integer.
        $length = \unpack('nlength', \substr($binary, 53, 2))['length'];
        $attestedCData->credentialId = \substr($binary, 55, $length);

        // set end offset
        $endOffset = 55 + $length;

        // extract public key
        $attestedCData->credentialPublicKey = $this->_readCredentialPublicKey($binary, 55 + $length, $endOffset);

        return $attestedCData;
    }

    /**
     * reads COSE key-encoded elliptic curve public key in EC2 format
     * @param string $binary
     * @param int $endOffset
     * @return \stdClass
     * @throws WebAuthnException
     */
    private function _readCredentialPublicKey($binary, $offset, &$endOffset) {
        $enc = CborDecoder::decodeInPlace($binary, $offset, $endOffset);

        // COSE key-encoded elliptic curve public key in EC2 format
        $credPKey = new \stdClass();
        $credPKey->kty = $enc[self::$_COSE_KTY];
        $credPKey->alg = $enc[self::$_COSE_ALG];

        switch ($credPKey->alg) {
            case self::$_EC2_ES256: $this->_readCredentialPublicKeyES256($credPKey, $enc); break;
            case self::$_RSA_RS256: $this->_readCredentialPublicKeyRS256($credPKey, $enc); break;
        }

        return $credPKey;
    }

    /**
     * extract ES256 informations from cose
     * @param \stdClass $credPKey
     * @param \stdClass $enc
     * @throws WebAuthnException
     */
    private function _readCredentialPublicKeyES256(&$credPKey, $enc) {
        $credPKey->crv = $enc[self::$_COSE_CRV];
        $credPKey->x   = $enc[self::$_COSE_X] instanceof ByteBuffer ? $enc[self::$_COSE_X]->getBinaryString() : null;
        $credPKey->y   = $enc[self::$_COSE_Y] instanceof ByteBuffer ? $enc[self::$_COSE_Y]->getBinaryString() : null;
        unset ($enc);

        // Validation
        if ($credPKey->kty !== self::$_EC2_TYPE) {
            throw new WebAuthnException('public key not in EC2 format', WebAuthnException::INVALID_PUBLIC_KEY);
        }

        if ($credPKey->alg !== self::$_EC2_ES256) {
            throw new WebAuthnException('signature algorithm not ES256', WebAuthnException::INVALID_PUBLIC_KEY);
        }

        if ($credPKey->crv !== self::$_EC2_P256) {
            throw new WebAuthnException('curve not P-256', WebAuthnException::INVALID_PUBLIC_KEY);
        }

        if (\strlen($credPKey->x) !== 32) {
            throw new WebAuthnException('Invalid X-coordinate', WebAuthnException::INVALID_PUBLIC_KEY);
        }

        if (\strlen($credPKey->y) !== 32) {
            throw new WebAuthnException('Invalid Y-coordinate', WebAuthnException::INVALID_PUBLIC_KEY);
        }
    }

    /**
     * extract RS256 informations from COSE
     * @param \stdClass $credPKey
     * @param \stdClass $enc
     * @throws WebAuthnException
     */
    private function _readCredentialPublicKeyRS256(&$credPKey, $enc) {
        $credPKey->n = $enc[self::$_COSE_N] instanceof ByteBuffer ? $enc[self::$_COSE_N]->getBinaryString() : null;
        $credPKey->e = $enc[self::$_COSE_E] instanceof ByteBuffer ? $enc[self::$_COSE_E]->getBinaryString() : null;
        unset ($enc);

        // Validation
        if ($credPKey->kty !== self::$_RSA_TYPE) {
            throw new WebAuthnException('public key not in RSA format', WebAuthnException::INVALID_PUBLIC_KEY);
        }

        if ($credPKey->alg !== self::$_RSA_RS256) {
            throw new WebAuthnException('signature algorithm not ES256', WebAuthnException::INVALID_PUBLIC_KEY);
        }

        if (\strlen($credPKey->n) !== 256) {
            throw new WebAuthnException('Invalid RSA modulus', WebAuthnException::INVALID_PUBLIC_KEY);
        }

        if (\strlen($credPKey->e) !== 3) {
            throw new WebAuthnException('Invalid RSA public exponent', WebAuthnException::INVALID_PUBLIC_KEY);
        }

    }

    /**
     * reads cbor encoded extension data.
     * @param string $binary
     * @return array
     * @throws WebAuthnException
     */
    private function _readExtensionData($binary) {
        $ext = CborDecoder::decode($binary);
        if (!\is_array($ext)) {
            throw new WebAuthnException('invalid extension data', WebAuthnException::INVALID_DATA);
        }

        return $ext;
    }


    // ---------------
    // DER functions
    // ---------------

    private function _der_length($len) {
        if ($len < 128) {
            return \chr($len);
        }
        $lenBytes = '';
        while ($len > 0) {
            $lenBytes = \chr($len % 256) . $lenBytes;
            $len = \intdiv($len, 256);
        }
        return \chr(0x80 | \strlen($lenBytes)) . $lenBytes;
    }

    private function _der_sequence($contents) {
        return "\x30" . $this->_der_length(\strlen($contents)) . $contents;
    }

    private function _der_oid($encoded) {
        return "\x06" . $this->_der_length(\strlen($encoded)) . $encoded;
    }

    private function _der_bitString($bytes) {
        return "\x03" . $this->_der_length(\strlen($bytes) + 1) . "\x00" . $bytes;
    }

    private function _der_nullValue() {
        return "\x05\x00";
    }

    private function _der_unsignedInteger($bytes) {
        $len = \strlen($bytes);

        // Remove leading zero bytes
        for ($i = 0; $i < ($len - 1); $i++) {
            if (\ord($bytes[$i]) !== 0) {
                break;
            }
        }
        if ($i !== 0) {
            $bytes = \substr($bytes, $i);
        }

        // If most significant bit is set, prefix with another zero to prevent it being seen as negative number
        if ((\ord($bytes[0]) & 0x80) !== 0) {
            $bytes = "\x00" . $bytes;
        }

        return "\x02" . $this->_der_length(\strlen($bytes)) . $bytes;
    }
}
