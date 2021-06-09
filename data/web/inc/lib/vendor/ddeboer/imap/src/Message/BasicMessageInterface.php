<?php

declare(strict_types=1);

namespace Ddeboer\Imap\Message;

interface BasicMessageInterface extends PartInterface
{
    /**
     * Get raw message headers.
     */
    public function getRawHeaders(): string;

    /**
     * Get the raw message, including all headers, parts, etc. unencoded and unparsed.
     *
     * @return string the raw message
     */
    public function getRawMessage(): string;

    /**
     * Get message headers.
     */
    public function getHeaders(): Headers;

    /**
     * Get message id.
     *
     * A unique message id in the form <...>
     */
    public function getId(): ?string;

    /**
     * Get message sender (from headers).
     */
    public function getFrom(): ?EmailAddress;

    /**
     * Get To recipients.
     *
     * @return EmailAddress[] Empty array in case message has no To: recipients
     */
    public function getTo(): array;

    /**
     * Get Cc recipients.
     *
     * @return EmailAddress[] Empty array in case message has no CC: recipients
     */
    public function getCc(): array;

    /**
     * Get Bcc recipients.
     *
     * @return EmailAddress[] Empty array in case message has no BCC: recipients
     */
    public function getBcc(): array;

    /**
     * Get Reply-To recipients.
     *
     * @return EmailAddress[] Empty array in case message has no Reply-To: recipients
     */
    public function getReplyTo(): array;

    /**
     * Get Sender.
     *
     * @return EmailAddress[] Empty array in case message has no Sender: recipients
     */
    public function getSender(): array;

    /**
     * Get Return-Path.
     *
     * @return EmailAddress[] Empty array in case message has no Return-Path: recipients
     */
    public function getReturnPath(): array;

    /**
     * Get date (from headers).
     */
    public function getDate(): ?\DateTimeImmutable;

    /**
     * Get message size (from headers).
     *
     * @return null|int|string
     */
    public function getSize();

    /**
     * Get message subject (from headers).
     */
    public function getSubject(): ?string;

    /**
     * Get message In-Reply-To (from headers).
     *
     * @return string[]
     */
    public function getInReplyTo(): array;

    /**
     * Get message References (from headers).
     *
     * @return string[]
     */
    public function getReferences(): array;

    /**
     * Get body HTML.
     *
     * @return null|string Null if message has no HTML message part
     */
    public function getBodyHtml(): ?string;

    /**
     * Get body text.
     */
    public function getBodyText(): ?string;

    /**
     * Get attachments (if any) linked to this e-mail.
     *
     * @return AttachmentInterface[]
     */
    public function getAttachments(): array;

    /**
     * Does this message have attachments?
     */
    public function hasAttachments(): bool;
}
