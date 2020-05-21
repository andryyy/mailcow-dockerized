<?php

declare(strict_types=1);

namespace Ddeboer\Imap\Search\Date;

use Ddeboer\Imap\Search\AbstractDate;

/**
 * Represents a date on condition. Messages must have a date matching the
 * specified date in order to match the condition.
 */
final class On extends AbstractDate
{
    /**
     * Returns the keyword that the condition represents.
     */
    protected function getKeyword(): string
    {
        return 'ON';
    }
}
