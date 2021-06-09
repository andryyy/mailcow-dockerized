<?php

declare(strict_types=1);

namespace Ddeboer\Imap;

use Ddeboer\Imap\Message\PartInterface;

/**
 * @extends \Iterator<MessageInterface>
 */
interface MessageIteratorInterface extends \Iterator
{
    /**
     * Get current message.
     *
     * @return MessageInterface<PartInterface>
     */
    public function current(): MessageInterface;
}
