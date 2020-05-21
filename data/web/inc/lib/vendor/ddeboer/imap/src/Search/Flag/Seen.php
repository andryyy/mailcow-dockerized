<?php

declare(strict_types=1);

namespace Ddeboer\Imap\Search\Flag;

use Ddeboer\Imap\Search\ConditionInterface;

/**
 * Represents an SEEN flag condition. Messages must have the \\SEEN flag
 * set in order to match the condition.
 */
final class Seen implements ConditionInterface
{
    /**
     * Returns the keyword that the condition represents.
     */
    public function toString(): string
    {
        return 'SEEN';
    }
}
