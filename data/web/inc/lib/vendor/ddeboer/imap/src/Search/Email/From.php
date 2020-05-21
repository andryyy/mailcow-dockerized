<?php

declare(strict_types=1);

namespace Ddeboer\Imap\Search\Email;

use Ddeboer\Imap\Search\AbstractText;

/**
 * Represents a "From" email address condition. Messages must have been sent
 * from the specified email address in order to match the condition.
 */
final class From extends AbstractText
{
    /**
     * Returns the keyword that the condition represents.
     */
    protected function getKeyword(): string
    {
        return 'FROM';
    }
}
