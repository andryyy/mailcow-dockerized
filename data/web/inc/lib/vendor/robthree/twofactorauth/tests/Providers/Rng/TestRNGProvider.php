<?php

namespace Tests\Providers\Rng;

use RobThree\Auth\Providers\Rng\IRNGProvider;

class TestRNGProvider implements IRNGProvider
{
    /** @var bool */
    private $isSecure;

    /**
     * @param bool $isSecure whether this provider is cryptographically secure
     */
    function __construct($isSecure = false)
    {
        $this->isSecure = $isSecure;
    }

    /**
     * {@inheritdoc}
     */
    public function getRandomBytes($bytecount)
    {
        $result = '';

        for ($i = 0; $i < $bytecount; $i++) {
            $result .= chr($i);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function isCryptographicallySecure()
    {
        return $this->isSecure;
    }
}
