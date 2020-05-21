<?php

declare(strict_types=1);

namespace Ddeboer\Imap\Search;

/**
 * Represents a text based condition. Text based conditions use a contains
 * restriction.
 */
abstract class AbstractText implements ConditionInterface
{
    /**
     * Text to be used for the condition.
     *
     * @var string
     */
    private $text;

    /**
     * Constructor.
     *
     * @param string $text optional text for the condition
     */
    public function __construct(string $text)
    {
        $this->text = $text;
    }

    /**
     * Converts the condition to a string that can be sent to the IMAP server.
     */
    final public function toString(): string
    {
        return \sprintf('%s "%s"', $this->getKeyword(), $this->text);
    }

    /**
     * Returns the keyword that the condition represents.
     */
    abstract protected function getKeyword(): string;
}
