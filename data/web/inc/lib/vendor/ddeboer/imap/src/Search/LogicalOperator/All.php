<?php

declare(strict_types=1);

namespace Ddeboer\Imap\Search\LogicalOperator;

use Ddeboer\Imap\Search\ConditionInterface;

/**
 * Represents an ALL operator. Messages must match all conditions following this
 * operator in order to match the expression.
 */
final class All implements ConditionInterface
{
    /**
     * Returns the keyword that the condition represents.
     */
    public function toString(): string
    {
        return 'ALL';
    }
}
