<?php

namespace RobThree\Auth\Providers\Time;

class LocalMachineTimeProvider implements ITimeProvider
{
    public function getTime()
    {
        return time();
    }
}
