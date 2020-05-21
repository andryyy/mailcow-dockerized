<?php

declare(strict_types=1);

namespace Ddeboer\Imap\Search\Date;

use Ddeboer\Imap\Search\AbstractDate;

/**
 * Represents a date before condition. Messages must have a date before the
 * specified date in order to match the condition.
 */
final class Before extends AbstractDate
{
    /**
     * Returns the keyword that the condition represents.
     */
    protected function getKeyword(): string
    {
        return 'BEFORE';
    }
}
