<?php

declare(strict_types=1);

namespace Ddeboer\Imap\Message;

final class EmbeddedMessage extends AbstractMessage implements EmbeddedMessageInterface
{
    /**
     * @var null|Headers
     */
    private $headers;

    /**
     * @var null|string
     */
    private $rawHeaders;

    /**
     * @var null|string
     */
    private $rawMessage;

    /**
     * Get message headers.
     */
    public function getHeaders(): Headers
    {
        if (null === $this->headers) {
            $this->headers = new Headers(\imap_rfc822_parse_headers($this->getRawHeaders()));
        }

        return $this->headers;
    }

    /**
     * Get raw message headers.
     */
    public function getRawHeaders(): string
    {
        if (null === $this->rawHeaders) {
            $rawHeaders       = \explode("\r\n\r\n", $this->getRawMessage(), 2);
            $this->rawHeaders = \current($rawHeaders);
        }

        return $this->rawHeaders;
    }

    /**
     * Get the raw message, including all headers, parts, etc. unencoded and unparsed.
     *
     * @return string the raw message
     */
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
