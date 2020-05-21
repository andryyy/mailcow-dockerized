<?php

declare(strict_types=1);

namespace Ddeboer\Imap\Message;

use Ddeboer\Imap\Exception\NotEmbeddedMessageException;

/**
 * An e-mail attachment.
 */
final class Attachment extends AbstractPart implements AttachmentInterface
{
    /**
     * Get attachment filename.
     */
    public function getFilename(): ?string
    {
        return $this->getParameters()->get('filename')
            ?: $this->getParameters()->get('name');
    }

    /**
     * Get attachment file size.
     *
     * @return null|int Number of bytes
     */
    public function getSize()
    {
        $size = $this->getParameters()->get('size');
        if (\is_numeric($size)) {
            $size = (int) $size;
        }

        return $size;
    }

    /**
     * Is this attachment also an Embedded Message?
     */
    public function isEmbeddedMessage(): bool
    {
        return self::TYPE_MESSAGE === $this->getType();
    }

    /**
     * Return embedded message.
     *
     * @throws NotEmbeddedMessageException
     */
    public function getEmbeddedMessage(): EmbeddedMessageInterface
    {
        if (!$this->isEmbeddedMessage()) {
            throw new NotEmbeddedMessageException(\sprintf(
                'Attachment "%s" in message "%s" is not embedded message',
                $this->getPartNumber(),
                $this->getNumber()
            ));
        }

        return new EmbeddedMessage($this->resource, $this->getNumber(), $this->getPartNumber(), $this->getStructure()->parts[0]);
    }
}
