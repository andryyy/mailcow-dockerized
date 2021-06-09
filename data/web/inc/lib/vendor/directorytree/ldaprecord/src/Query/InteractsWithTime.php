<?php

namespace LdapRecord\Query;

use Carbon\Carbon;
use DateInterval;
use DateTimeInterface;

/**
 * @author Taylor Otwell
 *
 * @link https://laravel.com
 */
trait InteractsWithTime
{
    /**
     * Get the "expires at" UNIX timestamp.
     *
     * @param DateTimeInterface|DateInterval|int $delay
     *
     * @return int
     */
    protected function expiresAt($delay = 0)
    {
        $delay = $this->parseDateInterval($delay);

        return $delay instanceof DateTimeInterface
            ? $delay->getTimestamp()
            : Carbon::now()->addRealSeconds($delay)->getTimestamp();
    }

    /**
     * If the given value is an interval, convert it to a DateTime instance.
     *
     * @param DateTimeInterface|DateInterval|int $delay
     *
     * @return DateTimeInterface|int
     */
    protected function parseDateInterval($delay)
    {
        if ($delay instanceof DateInterval) {
            $delay = Carbon::now()->add($delay);
        }

        return $delay;
    }

    /**
     * Get the current system time as a UNIX timestamp.
     *
     * @return int
     */
    protected function currentTime()
    {
        return Carbon::now()->getTimestamp();
    }
}
