<?php

namespace RobThree\Auth\Providers\Time;

interface ITimeProvider
{
    /**
     * @return int the current timestamp according to this provider
     */
    public function getTime();
}
