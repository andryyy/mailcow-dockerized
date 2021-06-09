<?php

declare(strict_types=1);

namespace Ddeboer\Imap\Message;

use Ddeboer\Imap\Exception\NotEmbeddedMessageException;

/**
 * An e-mail attachment.
 */
final class Attachment extends AbstractPart implements AttachmentInterface
{
    public function getFilename(): ?string
    {
        $filename = $this->getParameters()->get('filename');
        if (null === $filename || '' === $filename) {
            $filename = $this->getParameters()->get('name');
        }
        \assert(null === $filename || \is_string($filename));

        return $filename;
    }

    public function getSize()
    {
        $size = $this->getParameters()->get('size');
        if (\is_numeric($size)) {
            $size = (int) $size;
        }
        \assert(null === $size || \is_int($size));

        return $size;
    }

    public function isEmbeddedMessage(): bool
    {
        return self::TYPE_MESSAGE === $this->getType();
    }

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
