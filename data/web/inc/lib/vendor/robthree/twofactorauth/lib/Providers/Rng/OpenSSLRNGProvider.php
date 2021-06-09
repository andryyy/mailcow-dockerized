<?php

namespace RobThree\Auth\Providers\Rng;

class OpenSSLRNGProvider implements IRNGProvider
{
    /** @var bool */
    private $requirestrong;

    /**
     * @param bool $requirestrong
     */
    public function __construct($requirestrong = true)
    {
        $this->requirestrong = $requirestrong;
    }

    /**
     * {@inheritdoc}
     */
    public function getRandomBytes($bytecount)
    {
        $result = openssl_random_pseudo_bytes($bytecount, $crypto_strong);
        if ($this->requirestrong && ($crypto_strong === false)) {
            throw new RNGException('openssl_random_pseudo_bytes returned non-cryptographically strong value');
        }
        if ($result === false) {
            throw new RNGException('openssl_random_pseudo_bytes returned an invalid value');
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function isCryptographicallySecure()
    {
        return $this->requirestrong;
    }
}
