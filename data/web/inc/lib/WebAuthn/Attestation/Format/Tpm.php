<?php


namespace WebAuthn\Attestation\Format;
use WebAuthn\WebAuthnException;
use WebAuthn\Binary\ByteBuffer;

class Tpm extends FormatBase {
    private $_TPM_GENERATED_VALUE = "\xFF\x54\x43\x47";
    private $_TPM_ST_ATTEST_CERTIFY = "\x80\x17";
    private $_alg;
    private $_signature;
    private $_pubArea;
    private $_x5c;

    /**
     * @var ByteBuffer
     */
    private $_certInfo;


    public function __construct($AttestionObject, \WebAuthn\Attestation\AuthenticatorData $authenticatorData) {
        parent::__construct($AttestionObject, $authenticatorData);

        // check packed data
        $attStmt = $this->_attestationObject['attStmt'];

        if (!\array_key_exists('ver', $attStmt) || $attStmt['ver'] !== '2.0') {
            throw new WebAuthnException('invalid tpm version: ' . $attStmt['ver'], WebAuthnException::INVALID_DATA);
        }

        if (!\array_key_exists('alg', $attStmt) || $this->_getCoseAlgorithm($attStmt['alg']) === null) {
            throw new WebAuthnException('unsupported alg: ' . $attStmt['alg'], WebAuthnException::INVALID_DATA);
        }

        if (!\array_key_exists('sig', $attStmt) || !\is_object($attStmt['sig']) || !($attStmt['sig'] instanceof ByteBuffer)) {
            throw new WebAuthnException('signature not found', WebAuthnException::INVALID_DATA);
        }

        if (!\array_key_exists('certInfo', $attStmt) || !\is_object($attStmt['certInfo']) || !($attStmt['certInfo'] instanceof ByteBuffer)) {
            throw new WebAuthnException('certInfo not found', WebAuthnException::INVALID_DATA);
        }

        if (!\array_key_exists('pubArea', $attStmt) || !\is_object($attStmt['pubArea']) || !($attStmt['pubArea'] instanceof ByteBuffer)) {
            throw new WebAuthnException('pubArea not found', WebAuthnException::INVALID_DATA);
        }

        $this->_alg = $attStmt['alg'];
        $this->_signature = $attStmt['sig']->getBinaryString();
        $this->_certInfo = $attStmt['certInfo'];
        $this->_pubArea = $attStmt['pubArea'];

        // certificate for validation
        if (\array_key_exists('x5c', $attStmt) && \is_array($attStmt['x5c']) && \count($attStmt['x5c']) > 0) {

            // The attestation certificate attestnCert MUST be the first element in the array
            $attestnCert = array_shift($attStmt['x5c']);

            if (!($attestnCert instanceof ByteBuffer)) {
                throw new WebAuthnException('invalid x5c certificate', WebAuthnException::INVALID_DATA);
            }

            $this->_x5c = $attestnCert->getBinaryString();

            // certificate chain
            foreach ($attStmt['x5c'] as $chain) {
                if ($chain instanceof ByteBuffer) {
                    $this->_x5c_chain[] = $chain->getBinaryString();
                }
            }

        } else {
            throw new WebAuthnException('no x5c certificate found', WebAuthnException::INVALID_DATA);
        }
    }


    /*
     * returns the key certificate in PEM format
     * @return string|null
     */
    public function getCertificatePem() {
        if (!$this->_x5c) {
            return null;
        }
        return $this->_createCertificatePem($this->_x5c);
    }

    /**
     * @param string $clientDataHash
     */
    public function validateAttestation($clientDataHash) {
        return $this->_validateOverX5c($clientDataHash);
    }

    /**
     * validates the certificate against root certificates
     * @param array $rootCas
     * @return boolean
     * @throws WebAuthnException
     */
    public function validateRootCertificate($rootCas) {
        if (!$this->_x5c) {
            return false;
        }

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
     * validate if x5c is present
     * @param string $clientDataHash
     * @return bool
     * @throws WebAuthnException
     */
    protected function _validateOverX5c($clientDataHash) {
        $publicKey = \openssl_pkey_get_public($this->getCertificatePem());

        if ($publicKey === false) {
            throw new WebAuthnException('invalid public key: ' . \openssl_error_string(), WebAuthnException::INVALID_PUBLIC_KEY);
        }

        // Concatenate authenticatorData and clientDataHash to form attToBeSigned.
        $attToBeSigned = $this->_authenticatorData->getBinary();
        $attToBeSigned .= $clientDataHash;

        // Validate that certInfo is valid:

        // Verify that magic is set to TPM_GENERATED_VALUE.
        if ($this->_certInfo->getBytes(0, 4) !== $this->_TPM_GENERATED_VALUE) {
            throw new WebAuthnException('tpm magic not TPM_GENERATED_VALUE', WebAuthnException::INVALID_DATA);
        }

        // Verify that type is set to TPM_ST_ATTEST_CERTIFY.
        if ($this->_certInfo->getBytes(4, 2) !== $this->_TPM_ST_ATTEST_CERTIFY) {
            throw new WebAuthnException('tpm type not TPM_ST_ATTEST_CERTIFY', WebAuthnException::INVALID_DATA);
        }

        $offset = 6;
        $qualifiedSigner = $this->_tpmReadLengthPrefixed($this->_certInfo, $offset);
        $extraData = $this->_tpmReadLengthPrefixed($this->_certInfo, $offset);
        $coseAlg = $this->_getCoseAlgorithm($this->_alg);

        // Verify that extraData is set to the hash of attToBeSigned using the hash algorithm employed in "alg".
        if ($extraData->getBinaryString() !== \hash($coseAlg->hash, $attToBeSigned, true)) {
            throw new WebAuthnException('certInfo:extraData not hash of attToBeSigned', WebAuthnException::INVALID_DATA);
        }

        // Verify the sig is a valid signature over certInfo using the attestation
        // public key in aikCert with the algorithm specified in alg.
        return \openssl_verify($this->_certInfo->getBinaryString(), $this->_signature, $publicKey, $coseAlg->openssl) === 1;
    }


    /**
     * returns next part of ByteBuffer
     * @param ByteBuffer $buffer
     * @param int $offset
     * @return ByteBuffer
     */
    protected function _tpmReadLengthPrefixed(ByteBuffer $buffer, &$offset) {
        $len = $buffer->getUint16Val($offset);
        $data = $buffer->getBytes($offset + 2, $len);
        $offset += (2 + $len);

        return new ByteBuffer($data);
    }

}

