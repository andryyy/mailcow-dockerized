<?php

declare(strict_types=1);

namespace Ddeboer\Imap\Search\Text;

use Ddeboer\Imap\Search\AbstractText;

/**
 * Represents a message text contains condition. Messages must contain the
 * specified text in order to match the condition.
 */
final class Text extends AbstractText
{
    /**
     * Returns the keyword that the condition represents.
     */
    protected function getKeyword(): string
    {
        return 'TEXT';
    }
}
