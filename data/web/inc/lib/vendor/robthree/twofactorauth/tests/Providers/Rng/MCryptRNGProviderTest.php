<?php

namespace Tests\Providers\Rng;

use PHPUnit\Framework\TestCase;
use Tests\MightNotMakeAssertions;
use RobThree\Auth\Providers\Rng\MCryptRNGProvider;

class MCryptRNGProviderTest extends TestCase
{
    use NeedsRngLengths, MightNotMakeAssertions;

    /**
     * @requires function mcrypt_create_iv
     *
     * @return void
     */
    public function testMCryptRNGProvidersReturnExpectedNumberOfBytes()
    {
        if (function_exists('mcrypt_create_iv')) {
            $rng = new MCryptRNGProvider();

            foreach ($this->rngTestLengths as $l) {
                $this->assertEquals($l, strlen($rng->getRandomBytes($l)));
            }

            $this->assertTrue($rng->isCryptographicallySecure());
        } else {
            $this->noAssertionsMade();
        }
    }
}
