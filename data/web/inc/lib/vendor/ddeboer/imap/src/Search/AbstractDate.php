<?php

declare(strict_types=1);

namespace Ddeboer\Imap\Search;

use DateTimeInterface;

/**
 * Represents a date condition.
 */
abstract class AbstractDate implements ConditionInterface
{
    /**
     * Format for dates to be sent to the IMAP server.
     */
    private string $dateFormat;

    /**
     * The date to be used for the condition.
     */
    private DateTimeInterface $date;

    /**
     * Constructor.
     *
     * @param DateTimeInterface $date optional date for the condition
     */
    public function __construct(DateTimeInterface $date, string $dateFormat = 'j-M-Y')
    {
        $this->date       = $date;
        $this->dateFormat = $dateFormat;
    }

    /**
     * Converts the condition to a string that can be sent to the IMAP server.
     */
    final public function toString(): string
    {
        return \sprintf('%s "%s"', $this->getKeyword(), $this->date->format($this->dateFormat));
    }

    /**
     * Returns the keyword that the condition represents.
     */
    abstract protected function getKeyword(): string;
}
