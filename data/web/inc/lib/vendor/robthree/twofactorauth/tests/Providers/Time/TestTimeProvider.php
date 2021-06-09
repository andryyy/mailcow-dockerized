<?php

namespace Tests\Providers\Time;

use RobThree\Auth\Providers\Time\ITimeProvider;

class TestTimeProvider implements ITimeProvider
{
    /** @var int */
    private $time;

    /**
     * @param int $time
     */
    function __construct($time)
    {
        $this->time = $time;
    }

    /**
     * {@inheritdoc}
     */
    public function getTime()
    {
        return $this->time;
    }
}
