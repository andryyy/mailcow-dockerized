<?php

declare(strict_types=1);

namespace Ddeboer\Imap;

use Ddeboer\Imap\Exception\InvalidResourceException;

interface ImapResourceInterface
{
    /**
     * Get IMAP resource stream.
     *
     * @throws InvalidResourceException
     *
     * @return resource
     */
    public function getStream();

    /**
     * Clear last mailbox used cache.
     */
    public function clearLastMailboxUsedCache(): void;
}
