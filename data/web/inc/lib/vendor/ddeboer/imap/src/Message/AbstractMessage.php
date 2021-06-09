<?php

declare(strict_types=1);

namespace Ddeboer\Imap\Message;

use Ddeboer\Imap\Exception\InvalidDateHeaderException;

abstract class AbstractMessage extends AbstractPart
{
    /**
     * @var null|Attachment[]
     */
    private ?array $attachments = null;

    /**
     * Get message headers.
     */
    abstract public function getHeaders(): Headers;

    /**
     * Get message id.
     *
     * A unique message id in the form <...>
     */
    final public function getId(): ?string
    {
        $messageId = $this->getHeaders()->get('message_id');
        \assert(null === $messageId || \is_string($messageId));

        return $messageId;
    }

    /**
     * Get message sender (from headers).
     */
    final public function getFrom(): ?EmailAddress
    {
        $from = $this->getHeaders()->get('from');
        \assert(null === $from || \is_array($from));

        return null !== $from ? $this->decodeEmailAddress($from[0]) : null;
    }

    /**
     * Get To recipients.
     *
     * @return EmailAddress[] Empty array in case message has no To: recipients
     */
    final public function getTo(): array
    {
        $emails = $this->getHeaders()->get('to');
        \assert(null === $emails || \is_array($emails));

        return $this->decodeEmailAddresses($emails ?? []);
    }

    /**
     * Get Cc recipients.
     *
     * @return EmailAddress[] Empty array in case message has no CC: recipients
     */
    final public function getCc(): array
    {
        $emails = $this->getHeaders()->get('cc');
        \assert(null === $emails || \is_array($emails));

        return $this->decodeEmailAddresses($emails ?? []);
    }

    /**
     * Get Bcc recipients.
     *
     * @return EmailAddress[] Empty array in case message has no BCC: recipients
     */
    final public function getBcc(): array
    {
        $emails = $this->getHeaders()->get('bcc');
        \assert(null === $emails || \is_array($emails));

        return $this->decodeEmailAddresses($emails ?? []);
    }

    /**
     * Get Reply-To recipients.
     *
     * @return EmailAddress[] Empty array in case message has no Reply-To: recipients
     */
    final public function getReplyTo(): array
    {
        $emails = $this->getHeaders()->get('reply_to');
        \assert(null === $emails || \is_array($emails));

        return $this->decodeEmailAddresses($emails ?? []);
    }

    /**
     * Get Sender.
     *
     * @return EmailAddress[] Empty array in case message has no Sender: recipients
     */
    final public function getSender(): array
    {
        $emails = $this->getHeaders()->get('sender');
        \assert(null === $emails || \is_array($emails));

        return $this->decodeEmailAddresses($emails ?? []);
    }

    /**
     * Get Return-Path.
     *
     * @return EmailAddress[] Empty array in case message has no Return-Path: recipients
     */
    final public function getReturnPath(): array
    {
        $emails = $this->getHeaders()->get('return_path');
        \assert(null === $emails || \is_array($emails));

        return $this->decodeEmailAddresses($emails ?? []);
    }

    /**
     * Get date (from headers).
     */
    final public function getDate(): ?\DateTimeImmutable
    {
        /** @var null|string $dateHeader */
        $dateHeader = $this->getHeaders()->get('date');
        if (null === $dateHeader) {
            return null;
        }

        $alteredValue = $dateHeader;
        $alteredValue = \str_replace(',', '', $alteredValue);
        $alteredValue = (string) \preg_replace('/^[a-zA-Z]+ ?/', '', $alteredValue);
        $alteredValue = (string) \preg_replace('/\(.*\)/', '', $alteredValue);
        $alteredValue = (string) \preg_replace('/\<.*\>/', '', $alteredValue);
        $alteredValue = (string) \preg_replace('/\bUT\b/', 'UTC', $alteredValue);
        if (0 === \preg_match('/\d\d:\d\d:\d\d.* [\+\-]\d\d:?\d\d/', $alteredValue)) {
            $alteredValue .= ' +0000';
        }
        // Handle numeric months
        $alteredValue = (string) \preg_replace('/^(\d\d) (\d\d) (\d\d(?:\d\d)?) /', '$3-$2-$1 ', $alteredValue);

        try {
            $date = new \DateTimeImmutable($alteredValue);
        } catch (\Throwable $ex) {
            throw new InvalidDateHeaderException(\sprintf('Invalid Date header found: "%s"', $dateHeader), 0, $ex);
        }

        return $date;
    }

    /**
     * Get message size (from headers).
     *
     * @return null|int|string
     */
    final public function getSize()
    {
        $size = $this->getHeaders()->get('size');
        \assert(null === $size || \is_int($size) || \is_string($size));

        return $size;
    }

    /**
     * Get message subject (from headers).
     */
    final public function getSubject(): ?string
    {
        $subject = $this->getHeaders()->get('subject');
        \assert(null === $subject || \is_string($subject));

        return $subject;
    }

    /**
     * Get message In-Reply-To (from headers).
     *
     * @return string[]
     */
    final public function getInReplyTo(): array
    {
        $inReplyTo = $this->getHeaders()->get('in_reply_to');
        \assert(null === $inReplyTo || \is_string($inReplyTo));

        return null !== $inReplyTo ? \explode(' ', $inReplyTo) : [];
    }

    /**
     * Get message References (from headers).
     *
     * @return string[]
     */
    final public function getReferences(): array
    {
        $references = $this->getHeaders()->get('references');
        \assert(null === $references || \is_string($references));

        return null !== $references ? \explode(' ', $references) : [];
    }

    /**
     * Get body HTML.
     */
    final public function getBodyHtml(): ?string
    {
        $iterator = new \RecursiveIteratorIterator($this, \RecursiveIteratorIterator::SELF_FIRST);
        foreach ($iterator as $part) {
            if (self::SUBTYPE_HTML === $part->getSubtype()) {
                return $part->getDecodedContent();
            }
        }

        // If message has no parts and is HTML, return content of message itself.
        if (self::SUBTYPE_HTML === $this->getSubtype()) {
            return $this->getDecodedContent();
        }

        return null;
    }

    /**
     * Get body text.
     */
    final public function getBodyText(): ?string
    {
        $iterator = new \RecursiveIteratorIterator($this, \RecursiveIteratorIterator::SELF_FIRST);
        foreach ($iterator as $part) {
            if (self::SUBTYPE_PLAIN === $part->getSubtype()) {
                return $part->getDecodedContent();
            }
        }

        // If message has no parts, return content of message itself.
        if (self::SUBTYPE_PLAIN === $this->getSubtype()) {
            return $this->getDecodedContent();
        }

        return null;
    }

    /**
     * Get attachments (if any) linked to this e-mail.
     *
     * @return AttachmentInterface[]
     */
    final public function getAttachments(): array
    {
        if (null === $this->attachments) {
            $this->attachments = self::gatherAttachments($this);
        }

        return $this->attachments;
    }

    /**
     * @param PartInterface<PartInterface> $part
     *
     * @return Attachment[]
     */
    private static function gatherAttachments(PartInterface $part): array
    {
        $attachments = [];
        foreach ($part->getParts() as $childPart) {
            if ($childPart instanceof Attachment) {
                $attachments[] = $childPart;
            }
            if ($childPart->hasChildren()) {
                $attachments = \array_merge($attachments, self::gatherAttachments($childPart));
            }
        }

        return $attachments;
    }

    /**
     * Does this message have attachments?
     */
    final public function hasAttachments(): bool
    {
        return \count($this->getAttachments()) > 0;
    }

    /**
     * @param \stdClass[] $addresses
     *
     * @return EmailAddress[]
     */
    private function decodeEmailAddresses(array $addresses): array
    {
        $return = [];
        foreach ($addresses as $address) {
            if (isset($address->mailbox)) {
                $return[] = $this->decodeEmailAddress($address);
            }
        }

        return $return;
    }

    private function decodeEmailAddress(\stdClass $value): EmailAddress
    {
        return new EmailAddress($value->mailbox, $value->host, $value->personal);
    }
}
