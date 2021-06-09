<?php

declare(strict_types=1);

namespace Ddeboer\Imap\Message;

final class EmbeddedMessage extends AbstractMessage implements EmbeddedMessageInterface
{
    private ?Headers $headers   = null;
    private ?string $rawHeaders = null;
    private ?string $rawMessage = null;

    public function getHeaders(): Headers
    {
        if (null === $this->headers) {
            $this->headers = new Headers(\imap_rfc822_parse_headers($this->getRawHeaders()));
        }

        return $this->headers;
    }

    public function getRawHeaders(): string
    {
        if (null === $this->rawHeaders) {
            $rawHeaders       = \explode("\r\n\r\n", $this->getRawMessage(), 2);
            $this->rawHeaders = \current($rawHeaders);
        }

        return $this->rawHeaders;
    }

    public function getRawMessage(): string
    {
        if (null === $this->rawMessage) {
            $this->rawMessage = $this->doGetContent($this->getPartNumber());
        }

        return $this->rawMessage;
    }

    /**
     * Get content part number.
     */
    protected function getContentPartNumber(): string
    {
        $partNumber = $this->getPartNumber();
        if (0 === \count($this->getParts())) {
            $partNumber .= '.1';
        }

        return $partNumber;
    }
}
