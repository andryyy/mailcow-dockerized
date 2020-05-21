<?php

declare(strict_types=1);

namespace Ddeboer\Imap;

interface MessageIteratorInterface extends \Iterator
{
    /**
     * Get current message.
     */
    public function current(): MessageInterface;
}
