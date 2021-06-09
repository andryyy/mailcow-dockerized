<?php

namespace Tests\Providers\Rng;

trait NeedsRngLengths
{
    /** @var array */
    protected $rngTestLengths = array(1, 16, 32, 256);
}
