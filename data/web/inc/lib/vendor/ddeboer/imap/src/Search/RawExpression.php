<?php

declare(strict_types=1);

namespace Ddeboer\Imap\Search;

/**
 * Represents a raw expression.
 */
final class RawExpression implements ConditionInterface
{
    /**
     * Text to be used for the condition.
     */
    private string $expression;

    /**
     * @param string $expression text for the condition
     */
    public function __construct(string $expression)
    {
        $this->expression = $expression;
    }

    public function toString(): string
    {
        return $this->expression;
    }
}
