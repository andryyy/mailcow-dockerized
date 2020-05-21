<?php

declare(strict_types=1);

namespace Ddeboer\Imap\Search\Text;

use Ddeboer\Imap\Search\AbstractText;

/**
 * Represents a keyword text does not contain condition. Messages must not have
 * a keyword matching the specified text in order to match the condition.
 */
final class Unkeyword extends AbstractText
{
    /**
     * Returns the keyword that the condition represents.
     */
    protected function getKeyword(): string
    {
        return 'UNKEYWORD';
    }
}
