<?php


namespace WebAuthn\Attestation\Format;
use WebAuthn\WebAuthnException;


abstract class FormatBase {
    protected $_attestationObject = null;
    protected $_authenticatorData = null;
    protected $_x5c_chain = array();
    protected $_x5c_tempFile = null;

    /**
     *
     * @param Array $AttestionObject
     * @param \WebAuthn\Attestation\AuthenticatorData $authenticatorData
     */
    public function __construct($AttestionObject, \WebAuthn\Attestation\AuthenticatorData $authenticatorData) {
        $this->_attestationObject = $AttestionObject;
        $this->_authenticatorData = $authenticatorData;
    }

    /**
     *
     */
    public function __destruct() {
        // delete X.509 chain certificate file after use
        if (\is_file($this->_x5c_tempFile)) {
            \unlink($this->_x5c_tempFile);
        }
    }

    /**
     * returns the certificate chain in PEM format
     * @return string|null
     */
    public function getCertificateChain() {
        if (\is_file($this->_x5c_tempFile)) {
            return \file_get_contents($this->_x5c_tempFile);
        }
        return null;
    }

    /**
     * returns the key X.509 certificate in PEM format
     * @return string
     */
    public function getCertificatePem() {
        // need to be overwritten
        return null;
    }

    /**
     * checks validity of the signature
     * @param string $clientDataHash
     * @return bool
     * @throws WebAuthnException
     */
    public function validateAttestation($clientDataHash) {
        // need to be overwritten
        return false;
    }

    /**
     * validates the certificate against root certificates
     * @param array $rootCas
     * @return boolean
     * @throws WebAuthnException
     */
    public function validateRootCertificate($rootCas) {
        // need to be overwritten
        return false;
    }


    /**
     * create a PEM encoded certificate with X.509 binary data
     * @param string $x5c
     * @return string
     */
    protected function _createCertificatePem($x5c) {
        $pem = '-----BEGIN CERTIFICATE-----' . "\n";
        $pem .= \chunk_split(\base64_encode($x5c), 64, "\n");
        $pem .= '-----END CERTIFICATE-----' . "\n";
        return $pem;
    }

    /**
     * creates a PEM encoded chain file
     * @return type
     */
    protected function _createX5cChainFile() {
        $content = '';
        if (\is_array($this->_x5c_chain) && \count($this->_x5c_chain) > 0) {
            foreach ($this->_x5c_chain as $x5c) {
                $certInfo = \openssl_x509_parse($this->_createCertificatePem($x5c));
                // check if issuer = subject (self signed)
                if (\is_array($certInfo) && \is_array($certInfo['issuer']) && \is_array($certInfo['subject'])) {
                    $selfSigned = true;
                    foreach ($certInfo['issuer'] as $k => $v) {
                        if ($certInfo['subject'][$k] !== $v) {
                            $selfSigned = false;
                            break;
                        }
                    }

                    if (!$selfSigned) {
                        $content .= "\n" . $this->_createCertificatePem($x5c) . "\n";
                    }
                }
            }
        }

        if ($content) {
            $this->_x5c_tempFile = \sys_get_temp_dir() . '/x5c_chain_' . \base_convert(\rand(), 10, 36) . '.pem';
            if (\file_put_contents($this->_x5c_tempFile, $content) !== false) {
                return $this->_x5c_tempFile;
            }
        }

        return null;
    }


    /**
     * returns the name and openssl key for provided cose number.
     * @param int $coseNumber
     * @return \stdClass|null
     */
    protected function _getCoseAlgorithm($coseNumber) {
        // https://www.iana.org/assignments/cose/cose.xhtml#algorithms
        $coseAlgorithms = array(
            array(
                'hash' => 'SHA1',
                'openssl' => OPENSSL_ALGO_SHA1,
                'cose' => array(
                    -65535  // RS1
                )),

            array(
                'hash' => 'SHA256',
                'openssl' => OPENSSL_ALGO_SHA256,
                'cose' => array(
                    -257, // RS256
                    -37,  // PS256
                    -7,   // ES256
                    5     // HMAC256
                )),

            array(
                'hash' => 'SHA384',
                'openssl' => OPENSSL_ALGO_SHA384,
                'cose' => array(
                    -258, // RS384
                    -38,  // PS384
                    -35,  // ES384
                    6     // HMAC384
                )),

            array(
                'hash' => 'SHA512',
                'openssl' => OPENSSL_ALGO_SHA512,
                'cose' => array(
                    -259, // RS512
                    -39,  // PS512
                    -36,  // ES512
                    7     // HMAC512
                ))
        );

        foreach ($coseAlgorithms as $coseAlgorithm) {
            if (\in_array($coseNumber, $coseAlgorithm['cose'], true)) {
                $return = new \stdClass();
                $return->hash = $coseAlgorithm['hash'];
                $return->openssl = $coseAlgorithm['openssl'];
                return $return;
            }
        }

        return null;
    }
}
