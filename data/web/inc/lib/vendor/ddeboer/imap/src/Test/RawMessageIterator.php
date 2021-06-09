<?php

declare(strict_types=1);

namespace Ddeboer\Imap\Test;

use Ddeboer\Imap\Message\PartInterface;
use Ddeboer\Imap\MessageInterface;
use Ddeboer\Imap\MessageIteratorInterface;

/**
 * A MessageIterator to be used in a mocked environment.
 *
 * @extends \ArrayIterator<int, MessageInterface>
 */
final class RawMessageIterator extends \ArrayIterator implements MessageIteratorInterface
{
    /**
     * @return MessageInterface<PartInterface>
     */
    public function current(): MessageInterface
    {
        return parent::current();
    }
}
