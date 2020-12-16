<?php
namespace WebAuthn;

/**
 * @author Lukas Buchs
 * @license https://github.com/lbuchs/WebAuthn/blob/master/LICENSE MIT
 */
class WebAuthnException extends \Exception {
    const INVALID_DATA = 1;
    const INVALID_TYPE = 2;
    const INVALID_CHALLENGE = 3;
    const INVALID_ORIGIN = 4;
    const INVALID_RELYING_PARTY = 5;
    const INVALID_SIGNATURE = 6;
    const INVALID_PUBLIC_KEY = 7;
    const CERTIFICATE_NOT_TRUSTED = 8;
    const USER_PRESENT = 9;
    const USER_VERIFICATED = 10;
    const SIGNATURE_COUNTER = 11;
    const CRYPTO_STRONG = 13;
    const BYTEBUFFER = 14;
    const CBOR = 15;

    public function __construct($message = "", $code = 0, $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}
