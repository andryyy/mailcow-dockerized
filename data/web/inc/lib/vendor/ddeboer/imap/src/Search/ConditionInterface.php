<?php

declare(strict_types=1);

namespace Ddeboer\Imap\Search;

/**
 * Represents a condition that can be used in a search expression.
 */
interface ConditionInterface
{
    /**
     * Converts the condition to a string that can be sent to the IMAP server.
     */
    public function toString(): string;
}
