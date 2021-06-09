<?php

declare(strict_types=1);

namespace Ddeboer\Imap\Message;

use Ddeboer\Imap\Exception\NotEmbeddedMessageException;

/**
 * An e-mail attachment.
 */
interface AttachmentInterface extends PartInterface
{
    /**
     * Get attachment filename.
     */
    public function getFilename(): ?string;

    /**
     * Get attachment file size.
     *
     * @return null|int Number of bytes
     */
    public function getSize();

    /**
     * Is this attachment also an Embedded Message?
     */
    public function isEmbeddedMessage(): bool;

    /**
     * Return embedded message.
     *
     * @throws NotEmbeddedMessageException
     *
     * @return EmbeddedMessageInterface<PartInterface>
     */
    public function getEmbeddedMessage(): EmbeddedMessageInterface;
}
