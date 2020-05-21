<?php

declare(strict_types=1);

namespace Ddeboer\Imap\Search\Text;

use Ddeboer\Imap\Search\AbstractText;

/**
 * Represents a keyword text contains condition. Messages must have a keyword
 * matching the specified text in order to match the condition.
 */
final class Keyword extends AbstractText
{
    /**
     * Returns the keyword that the condition represents.
     */
    protected function getKeyword(): string
    {
        return 'KEYWORD';
    }
}
