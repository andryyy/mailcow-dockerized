<?php


namespace WebAuthn\Attestation\Format;
use WebAuthn\WebAuthnException;
use WebAuthn\Binary\ByteBuffer;

class AndroidSafetyNet extends FormatBase {
    private $_signature;
    private $_signedValue;
    private $_x5c;
    private $_payload;

    public function __construct($AttestionObject, \WebAuthn\Attestation\AuthenticatorData $authenticatorData) {
        parent::__construct($AttestionObject, $authenticatorData);

        // check data
        $attStmt = $this->_attestationObject['attStmt'];

        if (!\array_key_exists('ver', $attStmt) || !$attStmt['ver']) {
            throw new WebAuthnException('invalid Android Safety Net Format', WebAuthnException::INVALID_DATA);
        }

        if (!\array_key_exists('response', $attStmt) || !($attStmt['response'] instanceof ByteBuffer)) {
            throw new WebAuthnException('invalid Android Safety Net Format', WebAuthnException::INVALID_DATA);
        }

        $response = $attStmt['response']->getBinaryString();

        // Response is a JWS [RFC7515] object in Compact Serialization.
        // JWSs have three segments separated by two period ('.') characters
        $parts = \explode('.', $response);
        unset ($response);
        if (\count($parts) !== 3) {
            throw new WebAuthnException('invalid JWS data', WebAuthnException::INVALID_DATA);
        }

        $header = $this->_base64url_decode($parts[0]);
        $payload = $this->_base64url_decode($parts[1]);
        $this->_signature = $this->_base64url_decode($parts[2]);
        $this->_signedValue = $parts[0] . '.' . $parts[1];
        unset ($parts);

        $header = \json_decode($header);
        $payload = \json_decode($payload);

        if (!($header instanceof \stdClass)) {
            throw new WebAuthnException('invalid JWS header', WebAuthnException::INVALID_DATA);
        }
        if (!($payload instanceof \stdClass)) {
            throw new WebAuthnException('invalid JWS payload', WebAuthnException::INVALID_DATA);
        }

        if (!$header->x5c || !is_array($header->x5c) || count($header->x5c) === 0) {
            throw new WebAuthnException('No X.509 signature in JWS Header', WebAuthnException::INVALID_DATA);
        }

        // algorithm
        if (!\in_array($header->alg, array('RS256', 'ES256'))) {
            throw new WebAuthnException('invalid JWS algorithm ' . $header->alg, WebAuthnException::INVALID_DATA);
        }

        $this->_x5c = \base64_decode($header->x5c[0]);
        $this->_payload = $payload;

        if (count($header->x5c) > 1) {
            for ($i=1; $i<count($header->x5c); $i++) {
                $this->_x5c_chain[] = \base64_decode($header->x5c[$i]);
            }
            unset ($i);
        }
    }


    /*
     * returns the key certificate in PEM format
     * @return string
     */
    public function getCertificatePem() {
        return $this->_createCertificatePem($this->_x5c);
    }

    /**
     * @param string $clientDataHash
     */
    public function validateAttestation($clientDataHash) {
        $publicKey = \openssl_pkey_get_public($this->getCertificatePem());

        // Verify that the nonce in the response is identical to the Base64 encoding
        // of the SHA-256 hash of the concatenation of authenticatorData and clientDataHash.
        if (!$this->_payload->nonce || $this->_payload->nonce !== \base64_encode(\hash('SHA256', $this->_authenticatorData->getBinary() . $clientDataHash, true))) {
            throw new WebAuthnException('invalid nonce in JWS payload', WebAuthnException::INVALID_DATA);
        }

        // Verify that attestationCert is issued to the hostname "attest.android.com"
        $certInfo = \openssl_x509_parse($this->getCertificatePem());
        if (!\is_array($certInfo) || !$certInfo['subject'] || $certInfo['subject']['CN'] !== 'attest.android.com') {
            throw new WebAuthnException('invalid certificate CN in JWS (' . $certInfo['subject']['CN']. ')', WebAuthnException::INVALID_DATA);
        }

        // Verify that the ctsProfileMatch attribute in the payload of response is true.
        if (!$this->_payload->ctsProfileMatch) {
            throw new WebAuthnException('invalid ctsProfileMatch in payload', WebAuthnException::INVALID_DATA);
        }

        // check certificate
        return \openssl_verify($this->_signedValue, $this->_signature, $publicKey, OPENSSL_ALGO_SHA256) === 1;
    }


    /**
     * validates the certificate against root certificates
     * @param array $rootCas
     * @return boolean
     * @throws WebAuthnException
     */
    public function validateRootCertificate($rootCas) {
        $chainC = $this->_createX5cChainFile();
        if ($chainC) {
            $rootCas[] = $chainC;
        }

        $v = \openssl_x509_checkpurpose($this->getCertificatePem(), -1, $rootCas);
        if ($v === -1) {
            throw new WebAuthnException('error on validating root certificate: ' . \openssl_error_string(), WebAuthnException::CERTIFICATE_NOT_TRUSTED);
        }
        return $v;
    }


    /**
     * decode base64 url
     * @param string $data
     * @return string
     */
    private function _base64url_decode($data) {
        return \base64_decode(\strtr($data, '-_', '+/') . \str_repeat('=', 3 - (3 + \strlen($data)) % 4));
    }
}

