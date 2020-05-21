<?php

declare(strict_types=1);

namespace Ddeboer\Imap;

use Ddeboer\Imap\Search\ConditionInterface;

/**
 * Defines a search expression that can be used to look up email messages.
 */
final class SearchExpression implements ConditionInterface
{
    /**
     * The conditions that together represent the expression.
     *
     * @var array
     */
    private $conditions = [];

    /**
     * Adds a new condition to the expression.
     *
     * @param ConditionInterface $condition the condition to be added
     */
    public function addCondition(ConditionInterface $condition): self
    {
        $this->conditions[] = $condition;

        return $this;
    }

    /**
     * Converts the expression to a string that can be sent to the IMAP server.
     */
    public function toString(): string
    {
        $conditions = \array_map(static function (ConditionInterface $condition): string {
            return $condition->toString();
        }, $this->conditions);

        return \implode(' ', $conditions);
    }
}
