<?php

namespace Tests\Providers\Rng;

use PHPUnit\Framework\TestCase;
use RobThree\Auth\Providers\Rng\HashRNGProvider;

class HashRNGProviderTest extends TestCase
{
    use NeedsRngLengths;

    /**
     * @return void
     */
    public function testHashRNGProvidersReturnExpectedNumberOfBytes()
    {
        $rng = new HashRNGProvider();
        foreach ($this->rngTestLengths as $l) {
            $this->assertEquals($l, strlen($rng->getRandomBytes($l)));
        }

        $this->assertFalse($rng->isCryptographicallySecure());
    }
}
