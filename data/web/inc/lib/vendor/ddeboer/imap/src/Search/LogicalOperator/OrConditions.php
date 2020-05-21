<?php

declare(strict_types=1);

namespace Ddeboer\Imap\Search\LogicalOperator;

use Ddeboer\Imap\Search\ConditionInterface;

/**
 * Represents an OR operator. Messages only need to match one of the conditions
 * after this operator to match the expression.
 */
final class OrConditions implements ConditionInterface
{
    /**
     * The conditions that together represent the expression.
     *
     * @var array
     */
    private $conditions = [];

    public function __construct(array $conditions)
    {
        foreach ($conditions as $condition) {
            $this->addCondition($condition);
        }
    }

    /**
     * Adds a new condition to the expression.
     *
     * @param ConditionInterface $condition the condition to be added
     */
    private function addCondition(ConditionInterface $condition)
    {
        $this->conditions[] = $condition;
    }

    /**
     * Returns the keyword that the condition represents.
     */
    public function toString(): string
    {
        $conditions = \array_map(static function (ConditionInterface $condition): string {
            return $condition->toString();
        }, $this->conditions);

        return \sprintf('( %s )', \implode(' OR ', $conditions));
    }
}
