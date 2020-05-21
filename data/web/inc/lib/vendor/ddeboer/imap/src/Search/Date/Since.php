<?php

declare(strict_types=1);

namespace Ddeboer\Imap\Search\Date;

use Ddeboer\Imap\Search\AbstractDate;

/**
 * Represents a date after condition. Messages must have a date after the
 * specified date in order to match the condition.
 */
final class Since extends AbstractDate
{
    /**
     * Returns the keyword that the condition represents.
     */
    protected function getKeyword(): string
    {
        return 'SINCE';
    }
}
