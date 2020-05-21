<?php

declare(strict_types=1);

namespace Ddeboer\Imap\Search\Email;

use Ddeboer\Imap\Search\AbstractText;

/**
 * Represents a "To" email address condition. Messages must have been addressed
 * to the specified recipient (along with any others) in order to match the
 * condition.
 */
final class To extends AbstractText
{
    /**
     * Returns the keyword that the condition represents.
     */
    protected function getKeyword(): string
    {
        return 'TO';
    }
}
