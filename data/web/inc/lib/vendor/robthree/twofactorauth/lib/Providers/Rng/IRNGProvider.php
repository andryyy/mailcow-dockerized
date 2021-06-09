<?php

namespace RobThree\Auth\Providers\Rng;

interface IRNGProvider
{
    /**
     * @param int $bytecount the number of bytes of randomness to return
     *
     * @return string the random bytes
     */
    public function getRandomBytes($bytecount);

    /**
     * @return bool whether this provider is cryptographically secure
     */
    public function isCryptographicallySecure();
}
