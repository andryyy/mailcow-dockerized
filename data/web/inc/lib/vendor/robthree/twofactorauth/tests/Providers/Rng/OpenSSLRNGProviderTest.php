<?php

namespace Tests\Providers\Rng;

use PHPUnit\Framework\TestCase;
use RobThree\Auth\Providers\Rng\OpenSSLRNGProvider;

class OpenSSLRNGProviderTest extends TestCase
{
    use NeedsRngLengths;

    /**
     * @return void
     */
    public function testStrongOpenSSLRNGProvidersReturnExpectedNumberOfBytes()
    {
        $rng = new OpenSSLRNGProvider(true);
        foreach ($this->rngTestLengths as $l) {
            $this->assertEquals($l, strlen($rng->getRandomBytes($l)));
        }

        $this->assertTrue($rng->isCryptographicallySecure());
    }

    /**
     * @return void
     */
    public function testNonStrongOpenSSLRNGProvidersReturnExpectedNumberOfBytes()
    {
        $rng = new OpenSSLRNGProvider(false);
        foreach ($this->rngTestLengths as $l) {
            $this->assertEquals($l, strlen($rng->getRandomBytes($l)));
        }

        $this->assertFalse($rng->isCryptographicallySecure());
    }
}
