<?php

namespace RobThree\Auth\Providers\Rng;

class CSRNGProvider implements IRNGProvider
{
    /**
     * {@inheritdoc}
     */
    public function getRandomBytes($bytecount)
    {
        return random_bytes($bytecount);    // PHP7+
    }

    /**
     * {@inheritdoc}
     */
    public function isCryptographicallySecure()
    {
        return true;
    }
}
