<?php

namespace RobThree\Auth\Providers\Rng;

class MCryptRNGProvider implements IRNGProvider
{
    /** @var int */
    private $source;

    /**
     * @param int $source
     */
    public function __construct($source = MCRYPT_DEV_URANDOM)
    {
        $this->source = $source;
    }

    /**
     * {@inheritdoc}
     */
    public function getRandomBytes($bytecount)
    {
        $result = @mcrypt_create_iv($bytecount, $this->source);
        if ($result === false) {
            throw new RNGException('mcrypt_create_iv returned an invalid value');
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function isCryptographicallySecure()
    {
        return true;
    }
}
